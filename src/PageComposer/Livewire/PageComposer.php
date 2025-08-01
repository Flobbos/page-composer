<?php

namespace Flobbos\PageComposer\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

    public function mount($page = null)
    {
        $this->pageId = $page;
        $this->elements = Element::all();
        $this->setPageContent($this->pageId);

        if (request()->has('template')) {
            $this->loadTemplate(request()->get('template'));
        };

        $this->categories = Category::with('translations')->get();
        $this->tags = Tag::with('translations')->get();
    }

    public function render()
    {
        $this->hydrateLanguages();
        return view('page-composer::livewire.page-composer')->with([
            'templates' => PageTemplate::select('id', 'name')->get()
        ]);
    }

    public function columnWidth(int $size)
    {
        $sizes = [
            '12' => 'w-full',
            '11' => 'w-11/12',
            '10' => 'w-5/6',
            '9' => 'w-3/4',
            '8' => 'w-2/3',
            '7' => 'w-7/12',
            '6' => 'w-1/2',
            '5' => 'w-5/12',
            '4' => 'w-1/3',
            '3' => 'w-1/4',
            '2' => 'w-1/6',
            '1' => 'w-1/12',
        ];

        return $sizes[$size];
    }

    /**
     * Add a new language to the content
     *
     * @param integer $language_id
     * @return void
     */
    public function addLanguage(int $language_id): void
    {
        $selectedLanguage = Language::find($language_id);
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
        $this->currentLanguage = Language::find($language_id);
    }

    /**
     * Add a new row of content
     *
     * @return void
     */
    public function addRow(): void
    {
        $this->rows[$this->currentLanguage->locale]['rows'][] = [
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
        foreach ($rows as $row) {
            $this->rows[$this->currentLanguage->locale]['rows'][$row['value']]['sorting'] = $row['order'];
        }

        $this->rows[$this->currentLanguage->locale]['rows'] = array_values($this->rows[$this->currentLanguage->locale]['rows']);
    }

    /**
     * Computed property to get sorted rows
     *
     * @return array
     */
    public function getSortedRowsProperty(): array
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

        $this->validate();

        try {
            //Create page
            $this->page->published_on = $this->publishedOn;
            $this->page->category_id = $this->pageCategory['id'];
            $this->page->save();

            //Sync tags
            $selectedTags = [];

            // Set photos
            $this->page->photo = $this->photo;
            $this->page->newsletter_image = $this->newsletter_image;
            $this->page->slider_image = $this->slider_image;

            foreach ($this->pageTags as $tag) {
                $selectedTags[] = $tag['id'];
            }

            $this->page->tags()->sync($selectedTags);

            //Create page
            $this->page->published_on = $this->publishedOn;
            $this->page->category_id = $this->pageCategory['id'];

            // Save the page
            $this->page->save();

            //Sync tags
            $selectedTags = [];

            foreach ($this->pageTags as $tag) {
                $selectedTags[] = $tag['id'];
            }

            $this->page->tags()->sync($selectedTags);

            //Create page translations
            foreach ($this->pageTranslations as $key => $trans) {
                if (! empty($trans['language_id'])) {
                    $language = Language::where('locale', $key)->first();
                    $trans['slug'] = Str::slug(Arr::get($trans, 'content.title'));
                    $this->page->translations()->save(new PageTranslation(array_merge($trans, ['page_id' => $this->page->id])));
                }
            }
            //Save article content
            foreach ($this->rows as $lang => $langRow) {
                $language = Language::where('locale', $lang)->first();

                foreach (Arr::get($langRow, 'rows', []) as $row) {
                    $rowData = array_merge($row, ['page_id' => $this->page->id, 'language_id' => $language->id]);
                    $rowData['attributes'] = empty($rowData['attributes']) ? null : $rowData['attributes'];
                    $newRow = Row::create($rowData);

                    foreach (Arr::get($row, 'columns', []) as $key => $column) {
                        $newColumn = Column::create(array_merge($column, ['row_id' => $newRow->id]));

                        foreach (Arr::get($column, 'column_items', []) as $key => $item) {
                            $item = array_merge($item, ['column_id' => $newColumn->id]);
                            $newColumnItem = ColumnItem::create(array_merge($item, ['column_id' => $newColumn->id]));
                        }
                    }
                }
            }

            session()->flash('message', 'Page successfully saved.');

            if ($redirect) {
                return redirect()->route('page-composer::pages.index');
            } else {
                return redirect()->route('page-composer::pages.edit', $this->page->id);
            }
        } catch (Exception $ex) {
            session()->flash('error', $ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile());

            $this->exceptionMessage = $ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile();
        }
    }

    /**
     * Update content to DB
     */
    public function updateContent(bool $redirect)
    {
        $this->validate();

        try {
            // Set photos
            $this->page->photo = $this->photo;
            $this->page->newsletter_image = $this->newsletter_image;
            $this->page->slider_image = $this->slider_image;

            //Update page
            $this->page->published_on = $this->publishedOn;
            $this->page->category_id = $this->pageCategory['id'];

            // Save the page
            $this->page->save();

            //Sync tags
            $selectedTags = [];

            foreach ($this->pageTags as $tag) {
                $selectedTags[] = $tag['id'];
            }

            $this->page->tags()->sync($selectedTags);

            //Update page translations
            foreach ($this->pageTranslations as $key => $trans) {
                if (array_key_exists('id', $trans)) {
                    $translation = PageTranslation::find($trans['id']);
                    $trans['slug'] = Str::slug(Arr::get($trans, 'content.title'));
                    $translation->update($trans);
                } else {
                    $language = Language::where('locale', $key)->first();
                    $trans['slug'] = Str::slug(Arr::get($trans, 'content.title'));
                    $this->page->translations()->save(new PageTranslation(array_merge($trans, ['page_id' => $this->page->id])));
                }
            }
            //Update article content
            foreach ($this->rows as $lang => $langRow) {
                $language = Language::where('locale', $lang)->first();
                //Update rows
                foreach (Arr::get($langRow, 'rows', []) as $rowKey => $row) {
                    if (array_key_exists('id', $row)) {
                        $newRow = Row::find($row['id']);
                        $row['attributes'] = empty($row['attributes']) ? null : $row['attributes'];
                        $newRow->update($row);
                    } else {
                        $rowData = array_merge($row, ['page_id' => $this->page->id, 'language_id' => $language->id]);
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
            $this->exceptionMessage = $ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile();
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
        $this->elements = Element::all();
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
        $this->page->category_id = Arr::get($option, 'id');
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
        $this->rows[$this->currentLanguage->locale]['rows'] = array_values($this->rows[$this->currentLanguage->locale]['rows']);
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
            $this->page = $page;

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
                $this->rows[$row->language->locale]['rows'][] = $row->toArray();
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
            $this->displayDate = $this->page->published_on ? $this->page->published_on->format('m-d-Y') : null;
            $this->publishedOn = $this->page->published_on;
            //Get category
            $this->pageCategory = $page->category;
            //Get tags
            $this->pageTags = $page->tags;
        } elseif (is_null($this->page)) {
            $this->page = new Page();
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
        $this->languages = Language::all();
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
        $lang = Language::find($language_id);

        if (! isset($this->rows[$lang->locale])) {
            $this->rows[$lang->locale] = [
                'rows' => [],
            ];
        }
    }

    #[On('eventImageUploadComponentDeleted.pageComposer.mainPhoto')]
    public function handleImageUploadComponentDeletedPageComposerMainPhoto($imagePath, $itemIndex)
    {
        $this->photo = null;
        $this->page->photo = null;
    }

    #[On('eventImageUploadComponentSaved.pageComposer.mainPhoto')]
    public function handleImageUploadComponentSavedPageComposerMainPhoto($imagePath, $itemIndex)
    {
        $this->photo = $imagePath;
        $this->page->photo = $imagePath;
    }

    #[On('eventImageUploadComponentDeleted.pageComposer.newsletterImage')]
    public function handleImageUploadComponentDeletedPageComposerNewsletterImage($imagePath, $itemIndex)
    {
        $this->newsletter_image = null;
        $this->page->newsletter_image = null;
    }

    #[On('eventImageUploadComponentSaved.pageComposer.newsletterImage')]
    public function handleImageUploadComponentSavedPageComposerNewsletterImage($imagePath, $itemIndex)
    {
        $this->newsletter_image = $imagePath;
        $this->page->newsletter_image = $imagePath;
    }

    #[On('eventImageUploadComponentDeleted.pageComposer.sliderImage')]
    public function handleImageUploadComponentDeletedPageComposerSliderImage($imagePath, $itemIndex)
    {
        $this->slider_image = null;
        $this->page->slider_image = null;
    }

    #[On('eventImageUploadComponentSaved.pageComposer.sliderImage')]
    public function handleImageUploadComponentSavedPageComposerSliderImage($imagePath, $itemIndex)
    {
        $this->slider_image = $imagePath;
        $this->page->slider_image = $imagePath;
    }
}
