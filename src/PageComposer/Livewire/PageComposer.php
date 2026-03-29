<?php

namespace Flobbos\PageComposer\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Flobbos\PageComposer\Models\Row;
use Flobbos\PageComposer\Models\Tag;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\Column;
use Flobbos\PageComposer\Models\Element;
use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\ColumnItem;
use Flobbos\PageComposer\Models\PageTemplate;
use Flobbos\PageComposer\Models\PageTranslation;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class PageComposer extends Component
{
    private const ELEMENTS_CACHE_KEY = 'page-composer.elements';
    private const CATEGORIES_CACHE_KEY = 'page-composer.categories';
    private const TAGS_CACHE_KEY = 'page-composer.tags';
    private const LANGUAGES_CACHE_KEY = 'page-composer.languages';

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

    private function makePageModel(): Page
    {
        $page = $this->pageId ? Page::findOrFail($this->pageId) : new Page();

        $page->name = Arr::get($this->page, 'name');
        $page->photo = $this->photo;
        $page->newsletter_image = $this->newsletter_image;
        $page->slider_image = $this->slider_image;
        $page->published_on = $this->publishedOn;
        $page->category_id = Arr::get($this->pageCategory, 'id');

        return $page;
    }

    public function mount($page = null)
    {
        $this->pageId = $page;
        $this->elements = $this->getCachedElements();
        $this->setPageContent($this->pageId);

        if (request()->has('template')) {
            $this->loadTemplate(request()->get('template'));
        };

        $this->categories = $this->getCachedCategories();
        $this->tags = $this->getCachedTags();
    }

    private function getCachedElements(bool $refresh = false)
    {
        if ($refresh) {
            Cache::forget(self::ELEMENTS_CACHE_KEY);
        }

        return Cache::remember(self::ELEMENTS_CACHE_KEY, now()->addMinutes(30), function () {
            return Element::all();
        });
    }

    private function getCachedCategories(bool $refresh = false)
    {
        if ($refresh) {
            Cache::forget(self::CATEGORIES_CACHE_KEY);
        }

        return Cache::remember(self::CATEGORIES_CACHE_KEY, now()->addMinutes(30), function () {
            return Category::with('translations')->get();
        });
    }

    private function getCachedTags(bool $refresh = false)
    {
        if ($refresh) {
            Cache::forget(self::TAGS_CACHE_KEY);
        }

        return Cache::remember(self::TAGS_CACHE_KEY, now()->addMinutes(30), function () {
            return Tag::with('translations')->get();
        });
    }

    private function getCachedLanguages(bool $refresh = false)
    {
        if ($refresh) {
            Cache::forget(self::LANGUAGES_CACHE_KEY);
        }

        return Cache::remember(self::LANGUAGES_CACHE_KEY, now()->addMinutes(5), function () {
            return Language::all();
        });
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
        $selectedLanguage = $this->getCachedLanguages()->firstWhere('id', $language_id) ?? Language::find($language_id);

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
        $this->currentLanguage = $this->getCachedLanguages()->firstWhere('id', $language_id) ?? Language::find($language_id);
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
     * Update the sorting for the rows
     *
     * @param array $rows
     * @return void
     */
    public function updateRowSorting(array $rows): void
    {
        $locale = $this->currentLanguage->locale ?? null;
        if (!$locale) {
            return;
        }

        $currentRows = $this->rows[$locale]['rows'] ?? [];
        $indexBySortableKey = [];

        foreach ($currentRows as $index => $currentRow) {
            $indexBySortableKey[$this->rowSortableKey($currentRow, $index)] = $index;
        }

        foreach ($rows as $row) {
            $sortableKey = (string) Arr::get($row, 'value', '');
            if (!array_key_exists($sortableKey, $indexBySortableKey)) {
                continue;
            }

            $sourceIndex = $indexBySortableKey[$sortableKey];
            $this->rows[$locale]['rows'][$sourceIndex]['sorting'] = (int) Arr::get($row, 'order', $sourceIndex + 1);
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

    public function getSortedRowsProperty(): array
    {
        return $this->sortedRows();
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
            $page = $this->makePageModel();
            $page->save();
            $this->pageId = $page->id;

            //Sync tags
            $selectedTags = [];

            foreach ($this->pageTags as $tag) {
                $selectedTags[] = $tag['id'];
            }

            $page->tags()->sync($selectedTags);

            // Clear existing translations to avoid duplicates (idempotent)
            $page->translations()->delete();

            //Create page translations
            foreach ($this->pageTranslations as $key => $trans) {
                if (! empty($trans['language_id'])) {
                    $language = Language::where('locale', $key)->first();
                    $trans['slug'] = Str::slug(Arr::get($trans, 'content.title'));
                    $page->translations()->save(new PageTranslation(array_merge($trans, ['page_id' => $page->id])));
                }
            }

            // Clear existing rows to avoid duplicates (idempotent)
            // First get all row IDs for this page, then delete related column_items and columns
            $existingRows = Row::where('page_id', $page->id)->with('columns.column_items')->get();
            foreach ($existingRows as $existingRow) {
                foreach ($existingRow->columns as $column) {
                    $column->column_items()->delete();
                }
                $existingRow->columns()->delete();
            }
            Row::where('page_id', $page->id)->delete();

            //Save article content
            foreach ($this->rows as $lang => $langRow) {
                $language = Language::where('locale', $lang)->first();

                foreach (Arr::get($langRow, 'rows', []) as $row) {
                    $rowData = array_merge(Arr::except($row, ['uuid']), ['page_id' => $page->id, 'language_id' => $language->id]);
                    $rowData['attributes'] = empty($rowData['attributes']) ? null : $rowData['attributes'];
                    $rowData['available_space'] = $this->calculateRowAvailableSpace(Arr::get($row, 'columns', []));
                    $newRow = Row::create($rowData);

                    foreach (Arr::get($row, 'columns', []) as $key => $column) {
                        $newColumn = Column::create(array_merge($column, ['row_id' => $newRow->id]));

                        foreach (Arr::get($column, 'column_items', []) as $key => $item) {
                            $item = array_merge($item, ['column_id' => $newColumn->id]);
                            ColumnItem::create($item);
                        }
                    }
                }
            }

            session()->flash('message', 'Page successfully saved.');

            if ($redirect) {
                return redirect()->route('page-composer::pages.index');
            } else {
                return redirect()->route('page-composer::pages.edit', $page->id);
            }
        } catch (Exception $ex) {
            session()->flash('error', 'An error occurred while saving the page. Please try again.');

            $this->exceptionMessage = 'An error occurred while saving the page. Please try again.';
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
            $page = $this->makePageModel();
            $page->save();

            //Sync tags
            $selectedTags = [];

            foreach ($this->pageTags as $tag) {
                $selectedTags[] = $tag['id'];
            }

            $page->tags()->sync($selectedTags);

            //Update page translations
            foreach ($this->pageTranslations as $key => $trans) {
                if (array_key_exists('id', $trans)) {
                    $translation = PageTranslation::find($trans['id']);
                    $trans['slug'] = Str::slug(Arr::get($trans, 'content.title'));
                    $translation->update($trans);
                } else {
                    $language = Language::where('locale', $key)->first();
                    $trans['slug'] = Str::slug(Arr::get($trans, 'content.title'));
                    $page->translations()->save(new PageTranslation(array_merge($trans, ['page_id' => $page->id])));
                }
            }
            //Update article content
            foreach ($this->rows as $lang => $langRow) {
                $language = Language::where('locale', $lang)->first();
                //Update rows
                foreach (Arr::get($langRow, 'rows', []) as $rowKey => $row) {
                    $rowPayload = Arr::except($row, ['uuid']);
                    $rowPayload['available_space'] = $this->calculateRowAvailableSpace(Arr::get($row, 'columns', []));
                    if (array_key_exists('id', $row)) {
                        $newRow = Row::find($row['id']);
                        $rowPayload['attributes'] = empty($rowPayload['attributes']) ? null : $rowPayload['attributes'];
                        $newRow->update($rowPayload);
                    } else {
                        $rowData = array_merge($rowPayload, ['page_id' => $page->id, 'language_id' => $language->id]);
                        $rowData['attributes'] = empty($rowData['attributes']) ? null : $rowData['attributes'];
                        $newRow = Row::create($rowData);
                        // Set row ID in the row array
                        $row['id'] = $newRow->id;
                        // Update the row in the rows
                        $this->rows[$lang]['rows'][$rowKey] = $row;
                    }
                    //Update columns
                    foreach (Arr::get($row, 'columns', []) as $columnKey => $column) {
                        if (array_key_exists('id', $column)) {
                            $newColumn = Column::find($column['id']);
                            $newColumn->update($column);
                        } else {
                            $newColumn = Column::create(array_merge($column, ['row_id' => $newRow->id]));
                            // Set column ID in the column array
                            $column['id'] = $newColumn->id;
                            // Update the column in the rows
                            $this->rows[$lang]['rows'][$rowKey]['columns'][$columnKey] = $column;
                        }
                        //Update column items
                        foreach (Arr::get($column, 'column_items', []) as $itemKey => $item) {
                            if (array_key_exists('id', $item)) {
                                $newColumnItem = ColumnItem::find($item['id']);
                                $newColumnItem->update($item);
                            } else {
                                $item = array_merge($item, ['column_id' => $newColumn->id]);
                                $newColumnItem = ColumnItem::create(array_merge($item, ['column_id' => $newColumn->id]));
                                // Set column item ID in the item array
                                $item['id'] = $newColumnItem->id;
                                // Update the column item in column
                                $column['column_items'][$itemKey] = $item;
                                // Update the column in the rows
                                $this->rows[$lang]['rows'][$rowKey]['columns'][$columnKey] = $column;
                            }
                        }
                    }
                }
            }

            session()->flash('message', 'Page successfully updated.');

            if ($redirect) {
                return redirect()->route('page-composer::pages.index');
            } else {
                $this->dispatch('saved');
            }

            return;
        } catch (Exception $ex) {
            $this->showErrorMessage = true;
            $this->exceptionMessage = 'An error occurred while updating the page. Please try again.';
        }
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

        /* //Set element data
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
        } */
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
        $this->getCachedLanguages(true);
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
        $this->elements = $this->getCachedElements(true);
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
            // dd($this->rows['de']);
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
        $this->languages = $this->getCachedLanguages();
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
        $lang = $this->getCachedLanguages()->firstWhere('id', $language_id) ?? Language::find($language_id);

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
