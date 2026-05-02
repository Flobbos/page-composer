<?php

namespace Flobbos\PageComposer\Livewire\Concerns;

use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\PageTemplate;
use Illuminate\Support\Arr;

/**
 * @property array $rows
 * @property array $pageTranslations
 */
trait HandlesTemplates
{
    public $templateName;
    public $selectedTemplate;

    public function saveTemplate(): void
    {
        $this->validate([
            'templateName' => 'required',
        ], ['required' => '(required)']);

        $languages = collect($this->pageTranslations)
            ->pluck('language_id')
            ->all();

        $content = $this->rows;
        foreach ($content as $langKey => $lang) {
            foreach (Arr::get($lang, 'rows', []) as $rowKey => $row) {
                foreach (Arr::get($row, 'columns', []) as $columnKey => $column) {
                    foreach (Arr::get($column, 'column_items', []) as $itemKey => $item) {
                        $content[$langKey]['rows'][$rowKey]['columns'][$columnKey]['column_items'][$itemKey]['content'] = [];
                    }
                }
            }
        }

        PageTemplate::create([
            'name' => $this->templateName,
            'content' => $content,
            'languages' => $languages,
            'user_id' => auth()->id(),
        ]);

        $this->resetErrorBag();
        $this->reset('templateName');
        session()->flash('template_saved', '(saved)');
    }

    public function selectTemplate()
    {
        return redirect()->route('page-composer::pages.create', ['template' => $this->selectedTemplate]);
    }

    public function loadTemplate($templateId): void
    {
        $template = PageTemplate::find($templateId);
        $languages = Language::whereIn('id', $template->languages)->get();

        foreach ($languages as $lang) {
            $this->pageTranslations[$lang->locale] = [];
            $this->addLanguage($lang->id);
        }

        foreach ($template->content as $key => $rows) {
            $this->rows[$key] = $rows;
            $this->ensureUnsavedRowsHaveUuid($key);
        }
    }
}
