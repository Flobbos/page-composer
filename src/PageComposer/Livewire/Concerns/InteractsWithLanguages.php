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
        $this->availableLanguages = collect();
        $this->selectableLanguages = $this->languages;

        foreach ($this->pageTranslations as $trans) {
            if (isset($trans['language_id'])) {
                $this->availableLanguages->push($this->languages->where('id', $trans['language_id'])->first());
            }
        }

        foreach ($this->selectableLanguages as $key => $lang) {
            if ($this->availableLanguages->contains('id', $lang->id)) {
                $this->selectableLanguages->forget($key);
            }
        }
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
