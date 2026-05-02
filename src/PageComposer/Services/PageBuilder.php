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
                PageTranslation::find($trans['id'])?->update($trans);
                continue;
            }

            $page->translations()->save(new PageTranslation(array_merge($trans, ['page_id' => $page->id])));
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
                $rowPayload = Arr::except($row, ['uuid']);
                $rowPayload['available_space'] = $this->calculateAvailableSpace(Arr::get($row, 'columns', []));
                $rowPayload['attributes'] = empty($rowPayload['attributes']) ? null : $rowPayload['attributes'];

                if (array_key_exists('id', $row)) {
                    $rowModel = Row::find($row['id']);
                    $rowModel->update($rowPayload);
                } else {
                    $rowModel = Row::create(array_merge($rowPayload, [
                        'page_id' => $page->id,
                        'language_id' => $language->id,
                    ]));
                    $row['id'] = $rowModel->id;
                    $rows[$locale]['rows'][$rowKey] = $row;
                }

                foreach (Arr::get($row, 'columns', []) as $columnKey => $column) {
                    if (array_key_exists('id', $column)) {
                        Column::find($column['id'])?->update($column);
                        $columnId = $column['id'];
                    } else {
                        $columnModel = Column::create(array_merge($column, ['row_id' => $rowModel->id]));
                        $column['id'] = $columnModel->id;
                        $columnId = $columnModel->id;
                        $rows[$locale]['rows'][$rowKey]['columns'][$columnKey] = $column;
                    }

                    foreach (Arr::get($column, 'column_items', []) as $itemKey => $item) {
                        if (array_key_exists('id', $item)) {
                            ColumnItem::find($item['id'])?->update($item);
                            continue;
                        }

                        $itemModel = ColumnItem::create(array_merge($item, ['column_id' => $columnId]));
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
}
