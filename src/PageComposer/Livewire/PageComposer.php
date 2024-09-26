<?php

namespace Flobbos\PageComposer\Livewire;;

use Exception;
use Flobbos\PageComposer\Models\Row;
use Flobbos\PageComposer\Models\Tag;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\Column;
use Flobbos\PageComposer\Models\Element;
use Livewire\Component;
use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\ColumnItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Flobbos\PageComposer\Models\PageTranslation;

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

    protected $listeners = [
        'languageAdded',
        'componentAdded',
        'dateSelected',
        'categorySelected',
        'deleteRow',
        'rowUpdated',
        'columnUpdated' => 'rowUpdated',
        'tagsUpdated',
        'photoSaved' => 'setPhoto',
        'photoRemoved' => 'removePhoto'
    ];

    protected function rules()
    {
        return config('pagecomposer.rules');
    }

    public function mount($page = null)
    {
        $this->pageId = $page;
        $this->elements = Element::all();
        $this->hydratePage($this->pageId);
        $this->hydratePageTranslations();
        $this->categories = Category::with('translations')->get();
        $this->tags = Tag::with('translations')->get();
    }

    public function render()
    {
        $this->hydrateLanguages();
        return view('page-composer::livewire.page-composer');
    }

    public function columnWidth(int $size)
    {
        $sizes = [
            '12' => 'w-full',
            '6' => 'w-1/2',
            '3' => 'w-1/4'
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
            foreach ($this->pageTags as $tag) {
                $selectedTags[] = $tag['id'];
            }
            $this->page->tags()->sync($selectedTags);

            //Create page
            $this->page->published_on = $this->publishedOn;
            $this->page->category_id = $this->pageCategory['id'];
            $this->page->save();

            //Sync tags
            $selectedTags = [];
            foreach ($this->pageTags as $tag) {
                $selectedTags[] = $tag['id'];
            }
            $this->page->tags()->sync($selectedTags);

            //Create page translations
            foreach ($this->pageTranslations as $key => $trans) {
                $language = Language::where('locale', $key)->first();
                $trans['slug'] = Str::slug(Arr::get($trans, 'content.title'));
                $this->page->translations()->save(new PageTranslation(array_merge($trans, ['page_id' => $this->page->id])));
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
                return redirect()->route('pages.index');
            } else {
                return redirect()->route('pages.edit', $this->page->id);
            }
        } catch (Exception $ex) {
            $this->showErrorMessage = true;
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
            //Update page
            $this->page->published_on = $this->publishedOn;
            $this->page->category_id = $this->pageCategory['id'];
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
                foreach (Arr::get($langRow, 'rows', []) as $row) {
                    if (array_key_exists('id', $row)) {
                        $newRow = Row::find($row['id']);
                        $row['attributes'] = empty($row['attributes']) ? null : $row['attributes'];
                        $newRow->update($row);
                    } else {
                        $rowData = array_merge($row, ['page_id' => $this->page->id, 'language_id' => $language->id]);
                        $rowData['attributes'] = empty($rowData['attributes']) ? null : $rowData['attributes'];
                        $newRow = Row::create($rowData);
                    }
                    //Update columns
                    foreach (Arr::get($row, 'columns', []) as $key => $column) {
                        if (array_key_exists('id', $column)) {
                            $newColumn = Column::find($column['id']);
                            $newColumn->update($column);
                        } else {
                            $newColumn = Column::create(array_merge($column, ['row_id' => $newRow->id]));
                        }
                        //Update column items
                        foreach (Arr::get($column, 'column_items', []) as $key => $item) {
                            if (array_key_exists('id', $item)) {
                                $newColumnItem = ColumnItem::find($item['id']);
                                $newColumnItem->update($item);
                            } else {
                                $item = array_merge($item, ['column_id' => $newColumn->id]);
                                $newColumnItem = ColumnItem::create(array_merge($item, ['column_id' => $newColumn->id]));
                            }
                        }
                    }
                }
            }
            session()->flash('message', 'Page successfully updated.');
            if ($redirect) {
                return redirect()->route('pages.index');
            } else {
                $this->dispatchBrowserEvent('saved');
            }
            return;
        } catch (Exception $ex) {
            $this->showErrorMessage = true;
            $this->exceptionMessage = $ex->getMessage() . ' ' . $ex->getLine() . ' ' . $ex->getFile();
        }
    }

    /**
     * Set current photo
     *
     * @param string $target which photo is being set
     * @param string $filename filename for the photo
     * @return void
     */
    public function setPhoto(string $target, string $filename): void
    {
        $this->page->{$target} = $filename;
    }

    /**
     * Remove a photo from the page
     *
     * @param string $target which photo is being removed
     * @return void
     */
    public function removePhoto(string $target): void
    {
        $this->page->{$target} = null;
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

    /******* Listeners *******/
    /**
     * Listener for when a new language has been added
     *
     * @return void
     */
    public function languageAdded(): void
    {
        $this->hydrateLanguages();
    }

    /**
     * Refresh component list when a new one has been generated
     *
     * @return void
     */
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
    public function deleteRow(string $row_key): void
    {
        if (isset($this->rows[$this->currentLanguage->locale]['rows'][$row_key]['id'])) {
            if ($row = Row::find($this->rows[$this->currentLanguage->locale]['rows'][$row_key]['id'])) {
                $row->delete();
            }
        }
        unset($this->rows[$this->currentLanguage->locale]['rows'][$row_key]);
        $this->rows[$this->currentLanguage->locale]['rows'] = array_values($this->rows[$this->currentLanguage->locale]['rows']);
    }

    /**
     * Event listener for an update to the row data
     *
     * @param array $row
     * @param string $rowKey
     * @return void
     */
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
    private function hydratePage(int $id = null): void
    {
        if (!is_null($id)) {
            $page = Page::find($id);
            //Set basic page information
            $this->page = $page;
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
    private function hydrateLanguages(): void
    {
        //All languages
        $this->languages = Language::all();
        $this->availableLanguages = collect();
        $this->selectableLanguages = $this->languages;
        //Available languages in article
        foreach ($this->pageTranslations as $trans) {
            $this->availableLanguages->push($this->languages->where('id', $trans['language_id'])->first());
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
    private function setRowsLanguage(int $language_id)
    {
        $lang = Language::find($language_id);
        if (!isset($this->rows[$lang->locale])) {
            $this->rows[$lang->locale] = [
                'rows' => [],
            ];
        }
    }
}
