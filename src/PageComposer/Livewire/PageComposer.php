<?php

namespace Flobbos\PageComposer\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Flobbos\PageComposer\Models\Row;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\PageTemplate;
use Flobbos\PageComposer\Services\PageBuilder;
use Flobbos\PageComposer\Services\PageBuilderResult;
use Flobbos\PageComposer\Services\PageComposerCache;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class PageComposer extends Component
{
    //page
    public $elements, $page, $pageId, $pageCategory;
    public $pageTags = [];

    //language
    public $languages, $currentLanguage, $availableLanguages, $selectableLanguages;

    //photos
    public $photo, $newsletter_image, $slider_image;

    //options
    public $categories, $tags;

    //error handling
    public $exceptionMessage;
    public $showErrorMessage = false;

    //Settings
    public $settingsBox;
    public $showMiniMap = false;
    public $currentElement = ['name' => ''];

    //Preview
    public $previewMode = true;
    public $previewLanguage;

    public $rows = [];
    public $pageTranslations = [];

    public $displayDate, $publishedOn;

    //Template name
    public $templateName, $selectedTemplate;

    protected function rules()
    {
        return config('pagecomposer.rules');
    }

    private function syncPageState(): void
    {
        $this->page['photo'] = $this->photo;
        $this->page['newsletter_image'] = $this->newsletter_image;
        $this->page['slider_image'] = $this->slider_image;
        $this->page['published_on'] = $this->publishedOn;
        $this->page['category_id'] = Arr::get($this->pageCategory, 'id');
    }

    public function mount($page = null)
    {
        $this->pageId = $page;
        $this->elements = $this->cache()->elements();
        $this->setPageContent($this->pageId);

        if (request()->has('template')) {
            $this->loadTemplate(request()->get('template'));
        };

        $this->categories = $this->cache()->categories();
        $this->tags = $this->cache()->tags();
    }

    private function cache(): PageComposerCache
    {
        return app(PageComposerCache::class);
    }

    public function render()
    {
        $this->hydrateLanguages();
        return view('page-composer::livewire.page-composer')->with([
            'templates' => PageTemplate::select('id', 'name')->get()
        ]);
    }

    public function columnWidth(int $size): string
    {
        $sizes = config('pagecomposer.column_widths', [
            12 => 'w-full',
            11 => 'w-11/12',
            10 => 'w-5/6',
            9 => 'w-3/4',
            8 => 'w-2/3',
            7 => 'w-7/12',
            6 => 'w-1/2',
            5 => 'w-5/12',
            4 => 'w-1/3',
            3 => 'w-1/4',
            2 => 'w-1/6',
            1 => 'w-1/12',
        ]);

        return Arr::get($sizes, $size, 'w-full');
    }

    /**
     * Add a new language to the content
     *
     * @param integer $language_id
     * @return void
     */
    public function addLanguage(int $language_id): void
    {
        $selectedLanguage = $this->cache()->languages()->firstWhere('id', $language_id) ?? Language::find($language_id);

        if (!$selectedLanguage) {
            return;
        }

        //Set current active language if not present
        if (empty($this->currentLanguage)) {
            $this->currentLanguage = $selectedLanguage;
        }

        //if (!$this->pageId) {
        $this->pageTranslations[$selectedLanguage->locale]['language_id'] = $selectedLanguage->id;
        $this->setRowsLanguage($selectedLanguage->id);
        //}

        $this->hydrateLanguages();
    }

    /**
     * Set the current language for the content
     *
     * @param integer $language_id
     * @return void
     */
    public function setLanguage(int $language_id): void
    {
        $this->currentLanguage = $this->cache()->languages()->firstWhere('id', $language_id) ?? Language::find($language_id);
    }

    /**
     * Add a new row of content
     *
     * @return void
     */
    public function addRow(): void
    {
        $this->rows[$this->currentLanguage->locale]['rows'][] = [
            'uuid' => (string) Str::uuid(),
            'columns' => [],
            'attributes' => [],
            'alignment' => 'center',
            'expanded' => false,
            'active' => true,
            'sorting' => $this->rows[$this->currentLanguage->locale]['rows'] ? count($this->rows[$this->currentLanguage->locale]['rows']) + 1 : 1,
            'available_space' => 12
        ];
    }

    /**
     * Update the sorting for the rows. Called by Livewire 4's wire:sort when
     * a row is dropped in a new position in the mini map.
     *
     * @param string|int $id       sortable key from wire:sort:item (uuid, db id, or tmp-N)
     * @param int        $position zero-based target position
     */
    public function updateRowSorting($id, $position): void
    {
        $locale = $this->currentLanguage->locale ?? null;
        if (!$locale) {
            return;
        }

        $rows = $this->rows[$locale]['rows'] ?? [];
        if (empty($rows)) {
            return;
        }

        $indexByKey = [];
        foreach ($rows as $index => $row) {
            $indexByKey[$this->rowSortableKey($row, $index)] = $index;
        }

        $sourceKey = (string) $id;
        if (!array_key_exists($sourceKey, $indexByKey)) {
            return;
        }

        $orderedIndices = collect($rows)
            ->map(fn($row, $index) => ['index' => $index, 'sorting' => (int) Arr::get($row, 'sorting', 0)])
            ->sortBy('sorting')
            ->pluck('index')
            ->values()
            ->all();

        $sourceIndex = $indexByKey[$sourceKey];
        $currentPosition = array_search($sourceIndex, $orderedIndices, true);
        if ($currentPosition === false) {
            return;
        }

        array_splice($orderedIndices, $currentPosition, 1);
        array_splice($orderedIndices, max(0, (int) $position), 0, $sourceIndex);

        foreach ($orderedIndices as $newPosition => $rowIndex) {
            $this->rows[$locale]['rows'][$rowIndex]['sorting'] = $newPosition + 1;
        }
    }

    public function rowSortableKey(array $row, int $fallbackIndex): string
    {
        if (filled(Arr::get($row, 'id'))) {
            return (string) Arr::get($row, 'id');
        }

        if (filled(Arr::get($row, 'uuid'))) {
            return (string) Arr::get($row, 'uuid');
        }

        return 'tmp-' . $fallbackIndex;
    }

    /**
     * Computed property to get sorted rows
     *
     * @return array
     */
    #[Computed]
    public function sortedRows(): array
    {
        if (!isset($this->currentLanguage)) {
            return [];
        }

        if (empty($this->rows[$this->currentLanguage->locale]['rows'])) {
            return [];
        }

        return Arr::sort($this->rows[$this->currentLanguage->locale]['rows'], function ($value) {
            return $value['sorting'];
        });
    }

    /**
     * Sort column items
     *
     * @param array $items
     * @return array
     */
    public function sortItems(array $items): array
    {
        return Arr::sort($items, function ($value) {
            return $value['sorting'];
        });
    }

    /**
     * Save the current content to DB
     */
    public function saveContent(bool $redirect)
    {
        $this->syncPageState();
        $this->validate();

        try {
            $result = $this->persistPage();

            $this->pageId = $result->page->id;
            $this->rows = $result->rows;

            session()->flash('message', 'Page successfully saved.');

            if ($redirect) {
                return redirect()->route('page-composer::pages.index');
            }

            return redirect()->route('page-composer::pages.edit', $result->page->id);
        } catch (Exception $ex) {
            report($ex);
            $this->showErrorMessage = true;
            $this->exceptionMessage = 'We could not save this page. Please try again.';
            session()->flash('error', $this->exceptionMessage);
        }
    }

    /**
     * Update content to DB
     */
    public function updateContent(bool $redirect)
    {
        $this->syncPageState();
        $this->validate();

        try {
            $result = $this->persistPage();

            $this->pageId = $result->page->id;
            $this->rows = $result->rows;

            session()->flash('message', 'Page successfully updated.');

            if ($redirect) {
                return redirect()->route('page-composer::pages.index');
            }

            $this->dispatch('saved');
            return;
        } catch (Exception $ex) {
            report($ex);
            $this->showErrorMessage = true;
            $this->exceptionMessage = 'We could not update this page. Please try again.';
        }
    }

    private function persistPage(): PageBuilderResult
    {
        return app(PageBuilder::class)->persist(
            $this->pageId,
            $this->page ?? [],
            $this->pageTranslations,
            $this->pageTags ?? [],
            $this->rows,
            $this->cache()->languages()->keyBy('locale'),
        );
    }

    /**
     * Copy all content from one language to another
     *
     * @param string $source source language
     * @param string $target target language
     * @return void
     */
    public function copyContent(string $source, string $target): void
    {
        $this->rows[$target] = $this->rows[$source];
        //Remove database IDs from copied content
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

    /**
     * Save current page as template
     *
     * @return void
     */
    public function saveTemplate(): void
    {
        $this->validate([
            'templateName' => 'required'
        ], ['required' => '(required)']);

        //Set languages
        $languages = [];

        foreach ($this->pageTranslations as $trans) {
            $languages[] = Arr::get($trans, 'language_id');
        }

        //Set content structure
        $content = $this->rows;
        foreach ($content as $langKey => $lang) {
            foreach (Arr::get($lang, 'rows', []) as $key => $row) {
                foreach (Arr::get($row, 'columns', []) as $columnKey => $column) {
                    foreach (Arr::get($column, 'column_items', []) as $itemKey => $item) {
                        $content[$langKey]['rows'][$key]['columns'][$columnKey]['column_items'][$itemKey]['content'] = [];
                    }
                }
            }
        }
        //Create template
        PageTemplate::create([
            'name' => $this->templateName,
            'content' => $content,
            'languages' => $languages,
            'user_id' => auth()->id()
        ]);
        $this->resetErrorBag();
        $this->reset('templateName');
        session()->flash('template_saved', '(saved)');
    }

    public function selectTemplate()
    {
        return redirect()->route('page-composer::pages.create', ['template' => $this->selectedTemplate]);
    }

    public function loadTemplate($templateId)
    {
        //Load template informaiton
        $template = PageTemplate::find($templateId);
        //Load languages
        $languages = Language::whereIn('id', $template->languages)->get();
        //Set page translations
        foreach ($languages as $lang) {
            $this->pageTranslations[$lang->locale] = [];
            $this->addLanguage($lang->id);
        }

        //Set rows and columns
        foreach ($template->content as $key => $rows) {
            $this->rows[$key] = $rows;
            $this->ensureUnsavedRowsHaveUuid($key);
        }
    }

    /******* Listeners *******/
    /**
     * Listener for when a new language has been added
     *
     * @return void
     */
    #[On('languageAdded')]
    public function languageAdded(): void
    {
        // Language can be added during editing, so refresh this cache explicitly.
        $this->cache()->languages(true);
        $this->hydrateLanguages();
    }

    /**
     * Refresh component list when a new one has been generated
     *
     * @return void
     */
    #[On('componentAdded')]
    public function componentAdded(): void
    {
        $this->elements = $this->cache()->elements(true);
    }

    /**
     * Event emitted from date picker
     *
     * @param string $date
     * @return void
     */
    #[On('dateSelected')]
    public function dateSelected(string $date): void
    {
        $this->publishedOn = now()->createFromFormat('m-d-Y', $date);
        $this->displayDate = $this->publishedOn->format('m-d-Y');
    }

    /**
     * Event from category select field
     *
     * @param [type] $option
     * @return void
     */
    #[On('categorySelected')]
    public function categorySelected($option): void
    {
        $this->pageCategory = $option;
        $this->page['category_id'] = Arr::get($option, 'id');
    }

    /**
     * Event listener for when a row has been removed
     *
     * @param string $row_key
     * @return void
     */
    #[On('deleteRow')]
    public function deleteRow(string $rowKey): void
    {
        if (isset($this->rows[$this->currentLanguage->locale]['rows'][$rowKey]['id'])) {
            if ($row = Row::find($this->rows[$this->currentLanguage->locale]['rows'][$rowKey]['id'])) {
                $row->delete();
            }
        }
        unset($this->rows[$this->currentLanguage->locale]['rows'][$rowKey]);
    }

    /**
     * Event listener for an update to the row data
     *
     * @param array $row
     * @param string $rowKey
     * @return void
     */
    #[On('rowUpdated')]
    #[On('columnUpdated')]
    public function rowUpdated(array $row, string $rowKey): void
    {
        $this->rows[$this->currentLanguage->locale]['rows'][$rowKey] = $row;
    }

    /**
     * Event listener for an update to the tags associated with the page
     *
     * @param array $tags
     * @return void
     */
    #[On('tagsUpdated')]
    public function tagsUpdated(array $tags): void
    {
        $this->pageTags = $tags;
    }

    /******* Private functions  **********/

    /**
     * Hydrate the page with necessary basic values
     *
     * @param int $id
     * @return void
     */
    public function setPageContent(?int $id = null): void
    {
        if (!is_null($id)) {
            $page = Page::find($id);
            //Set basic page information
            $this->page = [
                'id' => $page->id,
                'name' => $page->name,
                'photo' => $page->photo,
                'newsletter_image' => $page->newsletter_image,
                'slider_image' => $page->slider_image,
                'published_on' => $page->published_on,
                'category_id' => $page->category_id,
            ];

            //Set photo
            $this->photo = $page->photo;
            $this->newsletter_image = $page->newsletter_image;
            $this->slider_image = $page->slider_image;
            //Load remaining data
            $page->load(['translations.language', 'category', 'tags', 'rows' => function ($q) {
                $q->with('language', 'columns.column_items.element');
            }]);
            //Set page translations
            foreach ($page->translations as $trans) {
                $this->pageTranslations[$trans->language->locale] = $trans->toArray();
                $this->addLanguage($trans->language->id);
            }
            //Set rows and columns
            foreach ($page->rows as $row) {
                $rowData = $row->toArray();
                $rowData['available_space'] = $this->calculateRowAvailableSpace(Arr::get($rowData, 'columns', []));
                $this->rows[$row->language->locale]['rows'][] = $rowData;
            }
            //Set element data
            foreach ($this->rows as $lang => $langRow) {
                foreach ($langRow['rows'] as $rowKey => $row) {
                    foreach ($row['columns'] as $columnKey => $column) {
                        foreach ($column['column_items'] as $itemKey => $item) {
                            $this->rows[$lang]['rows'][$rowKey]['columns'][$columnKey]['column_items'][$itemKey] = [
                                'element_id' => $item['element']['id'],
                                'id' => $item['id'],
                                'name' => $item['element']['name'],
                                'component' => $item['element']['component'],
                                'icon' => $item['element']['icon'],
                                'content' => $item['content'],
                                'attributes' => $item['attributes'],
                                'sorting' => $item['sorting'],
                                'active' => $item['active']
                            ];
                        }
                    }
                }
            }
            //Get publication date
            $this->displayDate = $page->published_on ? $page->published_on->format('m-d-Y') : null;
            $this->publishedOn = $page->published_on;
            //Get category
            $this->pageCategory = $page->category;
            //Get tags
            $this->pageTags = $page->tags;
        } elseif (is_null($this->page)) {
            $this->page = [
                'name' => null,
                'photo' => null,
                'newsletter_image' => null,
                'slider_image' => null,
                'published_on' => null,
                'category_id' => null,
            ];
        }
    }

    /**
     * Hydrate language specific arrays
     *
     * @return void
     */
    public function hydrateLanguages(): void
    {
        //All languages
        $this->languages = $this->cache()->languages();
        $this->availableLanguages = collect();
        $this->selectableLanguages = $this->languages;
        //Available languages in article

        foreach ($this->pageTranslations as $trans) {
            if (isset($trans['language_id'])) {
                $this->availableLanguages->push($this->languages->where('id', $trans['language_id'])->first());
            }
        }

        //Languages that can be added
        foreach ($this->selectableLanguages as $key => $lang) {
            if ($this->availableLanguages->contains('id', $lang->id)) {
                $this->selectableLanguages->forget($key);
            }
        }
    }

    /**
     * Set current language for the rows
     *
     * @param integer $language_id
     * @return void
     */
    public function setRowsLanguage(int $language_id)
    {
        $lang = $this->cache()->languages()->firstWhere('id', $language_id) ?? Language::find($language_id);

        if (!$lang) {
            return;
        }

        if (! isset($this->rows[$lang->locale])) {
            $this->rows[$lang->locale] = [
                'rows' => [],
            ];
        }

        $this->ensureUnsavedRowsHaveUuid($lang->locale);
    }

    private function ensureUnsavedRowsHaveUuid(string $locale): void
    {
        foreach (Arr::get($this->rows, $locale . '.rows', []) as $rowKey => $row) {
            $this->rows[$locale]['rows'][$rowKey]['available_space'] = $this->calculateRowAvailableSpace(Arr::get($row, 'columns', []));

            if (filled(Arr::get($row, 'id')) || filled(Arr::get($row, 'uuid'))) {
                continue;
            }

            $this->rows[$locale]['rows'][$rowKey]['uuid'] = (string) Str::uuid();
        }
    }

    private function calculateRowAvailableSpace(array $columns): int
    {
        return max(0, 12 - (int) collect($columns)
            ->sum(fn($column) => (int) Arr::get($column, 'column_size', 0)));
    }

    #[On('eventImageUploadComponentDeleted.pageComposer.mainPhoto')]
    public function handleImageUploadComponentDeletedPageComposerMainPhoto($imagePath, $itemIndex)
    {
        $this->photo = null;
        $this->page['photo'] = null;
    }

    #[On('eventImageUploadComponentSaved.pageComposer.mainPhoto')]
    public function handleImageUploadComponentSavedPageComposerMainPhoto($imagePath, $itemIndex)
    {
        $this->photo = $imagePath;
        $this->page['photo'] = $imagePath;
    }

    #[On('eventImageUploadComponentDeleted.pageComposer.newsletterImage')]
    public function handleImageUploadComponentDeletedPageComposerNewsletterImage($imagePath, $itemIndex)
    {
        $this->newsletter_image = null;
        $this->page['newsletter_image'] = null;
    }

    #[On('eventImageUploadComponentSaved.pageComposer.newsletterImage')]
    public function handleImageUploadComponentSavedPageComposerNewsletterImage($imagePath, $itemIndex)
    {
        $this->newsletter_image = $imagePath;
        $this->page['newsletter_image'] = $imagePath;
    }

    #[On('eventImageUploadComponentDeleted.pageComposer.sliderImage')]
    public function handleImageUploadComponentDeletedPageComposerSliderImage($imagePath, $itemIndex)
    {
        $this->slider_image = null;
        $this->page['slider_image'] = null;
    }

    #[On('eventImageUploadComponentSaved.pageComposer.sliderImage')]
    public function handleImageUploadComponentSavedPageComposerSliderImage($imagePath, $itemIndex)
    {
        $this->slider_image = $imagePath;
        $this->page['slider_image'] = $imagePath;
    }
}
