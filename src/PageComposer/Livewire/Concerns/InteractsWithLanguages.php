<?php

namespace Flobbos\PageComposer\Livewire\Concerns;

use Flobbos\PageComposer\Models\Language;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

/**
 * @property array $rows
 * @property array $pageTranslations
 */
trait InteractsWithLanguages
{
    #[Locked]
    public $languages;

    #[Locked]
    public $currentLanguage;

    #[Locked]
    public $availableLanguages;

    #[Locked]
    public $selectableLanguages;

    public function addLanguage(int $language_id): void
    {
        $selectedLanguage = $this->cache()->languages()->firstWhere('id', $language_id) ?? Language::find($language_id);

        if (!$selectedLanguage) {
            return;
        }

        if (empty($this->currentLanguage)) {
            $this->currentLanguage = $selectedLanguage;
        }

        $this->pageTranslations[$selectedLanguage->locale]['language_id'] = $selectedLanguage->id;
        $this->setRowsLanguage($selectedLanguage->id);

        $this->hydrateLanguages();
    }

    public function setLanguage(int $language_id): void
    {
        $this->currentLanguage = $this->cache()->languages()->firstWhere('id', $language_id) ?? Language::find($language_id);
    }

    /**
     * Copy all content from one language to another.
     */
    public function copyContent(string $source, string $target): void
    {
        $this->rows[$target] = $this->rows[$source];

        foreach ($this->rows[$target]['rows'] as $rowKey => $row) {
            Arr::forget($this->rows, $target . '.rows.' . $rowKey . '.id');
            $this->rows[$target]['rows'][$rowKey]['uuid'] = (string) Str::uuid();
            foreach (Arr::get($row, 'columns', []) as $columnKey => $column) {
                Arr::forget($this->rows, $target . '.rows.' . $rowKey . '.columns.' . $columnKey . '.id');
                foreach (Arr::get($column, 'column_items', []) as $itemKey => $item) {
                    Arr::forget($this->rows, $target . '.rows.' . $rowKey . '.columns.' . $columnKey . '.column_items.' . $itemKey . '.id');
                }
            }
        }

        $language = Language::where('locale', $target)->first();
        $this->addLanguage($language->id);
    }

    #[On('languageAdded')]
    public function languageAdded(): void
    {
        // Language can be added during editing, so refresh this cache explicitly.
        $this->cache()->languages(true);
        $this->hydrateLanguages();
    }

    public function hydrateLanguages(): void
    {
        $this->languages = $this->cache()->languages();

        // IDs of languages already in use by the page (one per translation
        // that carries a language_id).
        $usedIds = collect($this->pageTranslations)
            ->pluck('language_id')
            ->filter()
            ->values();

        // Derive both lists with non-mutating queries. Assigning the cached
        // collection to $selectableLanguages and then forget()-ing from it
        // would mutate the shared instance and drop the selected languages
        // from the master $languages list too.
        $this->availableLanguages = $this->languages->whereIn('id', $usedIds)->values();
        $this->selectableLanguages = $this->languages->whereNotIn('id', $usedIds)->values();
    }

    public function setRowsLanguage(int $language_id): void
    {
        $lang = $this->cache()->languages()->firstWhere('id', $language_id) ?? Language::find($language_id);

        if (!$lang) {
            return;
        }

        if (!isset($this->rows[$lang->locale])) {
            $this->rows[$lang->locale] = [
                'rows' => [],
            ];
        }

        $this->ensureUnsavedRowsHaveUuid($lang->locale);
    }
}
