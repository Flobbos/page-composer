<?php

namespace Flobbos\PageComposer\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Flobbos\PageComposer\Models\Column;
use Flobbos\PageComposer\Models\ColumnItem;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTranslation;
use Flobbos\PageComposer\Models\Row;

class PageBuilder
{
    public function persist(
        ?int $pageId,
        array $pageData,
        array $pageTranslations,
        array $pageTags,
        array $rows,
        Collection $languagesByLocale,
    ): PageBuilderResult {
        return DB::transaction(function () use ($pageId, $pageData, $pageTranslations, $pageTags, $rows, $languagesByLocale) {
            $page = $this->upsertPage($pageId, $pageData);

            $page->tags()->sync(collect($pageTags)->pluck('id')->all());

            $this->upsertTranslations($page, $pageTranslations);

            $rows = $this->upsertRows($page, $rows, $languagesByLocale);

            $this->purgeOrphans($page, $rows, $languagesByLocale);

            return new PageBuilderResult($page, $rows);
        });
    }

    private function upsertPage(?int $pageId, array $pageData): Page
    {
        $page = $pageId ? Page::findOrFail($pageId) : new Page();

        $page->name = Arr::get($pageData, 'name');
        $page->photo = Arr::get($pageData, 'photo');
        $page->newsletter_image = Arr::get($pageData, 'newsletter_image');
        $page->slider_image = Arr::get($pageData, 'slider_image');
        $page->published_on = Arr::get($pageData, 'published_on');
        $page->category_id = Arr::get($pageData, 'category_id');
        $page->save();

        return $page;
    }

    private function upsertTranslations(Page $page, array $translations): void
    {
        foreach ($translations as $trans) {
            if (empty($trans['language_id'])) {
                continue;
            }

            $trans['slug'] = Str::slug(Arr::get($trans, 'content.title'));

            if (array_key_exists('id', $trans)) {
                // Scope the lookup to this page so a stale or tampered id can
                // never update another page's translation.
                $existing = $page->translations()->whereKey($trans['id'])->first();

                if ($existing) {
                    $existing->update(Arr::except($trans, ['id', 'page_id']));
                    continue;
                }
            }

            $page->translations()->save(
                new PageTranslation(array_merge(Arr::except($trans, ['id']), ['page_id' => $page->id]))
            );
        }
    }

    private function upsertRows(Page $page, array $rows, Collection $languagesByLocale): array
    {
        foreach ($rows as $locale => $langRow) {
            $language = $languagesByLocale->get($locale);
            if (!$language) {
                continue;
            }

            foreach (Arr::get($langRow, 'rows', []) as $rowKey => $row) {
                // Ownership keys (id/page_id/language_id) are never taken from
                // client state on update: lookups are scoped to the parent and
                // ownership is assigned by the builder, so a stale or tampered
                // id can only ever touch records that belong to this page.
                $rowPayload = Arr::except($row, ['uuid', 'id', 'page_id', 'language_id']);
                $rowPayload['available_space'] = $this->calculateAvailableSpace(Arr::get($row, 'columns', []));
                $rowPayload['attributes'] = empty($rowPayload['attributes']) ? null : $rowPayload['attributes'];

                $rowModel = array_key_exists('id', $row)
                    ? $page->rows()->whereKey($row['id'])->first()
                    : null;

                if ($rowModel) {
                    $rowModel->update($rowPayload);
                } else {
                    $rowModel = Row::create(array_merge($rowPayload, [
                        'page_id' => $page->id,
                        'language_id' => $language->id,
                    ]));
                }

                $row['id'] = $rowModel->id;
                $rows[$locale]['rows'][$rowKey] = $row;

                foreach (Arr::get($row, 'columns', []) as $columnKey => $column) {
                    $columnPayload = Arr::except($column, ['id', 'row_id']);

                    $columnModel = array_key_exists('id', $column)
                        ? $rowModel->columns()->whereKey($column['id'])->first()
                        : null;

                    if ($columnModel) {
                        $columnModel->update($columnPayload);
                    } else {
                        $columnModel = Column::create(array_merge($columnPayload, ['row_id' => $rowModel->id]));
                    }

                    $column['id'] = $columnModel->id;
                    $rows[$locale]['rows'][$rowKey]['columns'][$columnKey] = $column;

                    foreach (Arr::get($column, 'column_items', []) as $itemKey => $item) {
                        $itemPayload = Arr::except($item, ['id', 'column_id']);

                        $itemModel = array_key_exists('id', $item)
                            ? $columnModel->column_items()->whereKey($item['id'])->first()
                            : null;

                        if ($itemModel) {
                            $itemModel->update($itemPayload);
                        } else {
                            $itemModel = ColumnItem::create(array_merge($itemPayload, ['column_id' => $columnModel->id]));
                        }

                        $item['id'] = $itemModel->id;
                        $column['column_items'][$itemKey] = $item;
                        $rows[$locale]['rows'][$rowKey]['columns'][$columnKey] = $column;
                    }
                }
            }
        }

        return $rows;
    }

    private function calculateAvailableSpace(array $columns): int
    {
        return max(0, 12 - (int) collect($columns)
            ->sum(fn($column) => (int) Arr::get($column, 'column_size', 0)));
    }

    /**
     * Delete rows / columns / column items that exist in the database for
     * this page but were dropped from the in-memory state since load.
     * Structural deletes are staged in the editor (no immediate DB
     * delete) and applied here on save. Runs inside the transaction so
     * the purge rolls back if anything else fails.
     */
    private function purgeOrphans(Page $page, array $rows, Collection $languagesByLocale): void
    {
        foreach ($rows as $locale => $langRow) {
            $language = $languagesByLocale->get($locale);
            if (!$language) {
                continue;
            }

            $keptRowIds = collect(Arr::get($langRow, 'rows', []))
                ->pluck('id')
                ->filter()
                ->values();

            $orphanedRows = Row::where('page_id', $page->id)
                ->where('language_id', $language->id);

            if ($keptRowIds->isNotEmpty()) {
                $orphanedRows->whereNotIn('id', $keptRowIds);
            }

            $orphanedRows->delete();

            foreach (Arr::get($langRow, 'rows', []) as $row) {
                if (!array_key_exists('id', $row)) {
                    continue;
                }

                $keptColumnIds = collect(Arr::get($row, 'columns', []))
                    ->pluck('id')
                    ->filter()
                    ->values();

                $orphanedColumns = Column::where('row_id', $row['id']);

                if ($keptColumnIds->isNotEmpty()) {
                    $orphanedColumns->whereNotIn('id', $keptColumnIds);
                }

                $orphanedColumns->delete();

                foreach (Arr::get($row, 'columns', []) as $column) {
                    if (!array_key_exists('id', $column)) {
                        continue;
                    }

                    $keptItemIds = collect(Arr::get($column, 'column_items', []))
                        ->pluck('id')
                        ->filter()
                        ->values();

                    $orphanedItems = ColumnItem::where('column_id', $column['id']);

                    if ($keptItemIds->isNotEmpty()) {
                        $orphanedItems->whereNotIn('id', $keptItemIds);
                    }

                    $orphanedItems->delete();
                }
            }
        }
    }
}
