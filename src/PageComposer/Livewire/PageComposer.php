<?php

namespace Flobbos\PageComposer\Livewire;

use Exception;
use Livewire\Component;
use Illuminate\Support\Arr;
use Flobbos\PageComposer\Livewire\Concerns\HandlesImageUploads;
use Flobbos\PageComposer\Livewire\Concerns\HandlesTemplates;
use Flobbos\PageComposer\Livewire\Concerns\InteractsWithLanguages;
use Flobbos\PageComposer\Livewire\Concerns\ManagesRows;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTemplate;
use Flobbos\PageComposer\Services\PageBuilder;
use Flobbos\PageComposer\Services\PageBuilderResult;
use Flobbos\PageComposer\Services\PageComposerCache;
use Livewire\Attributes\On;

class PageComposer extends Component
{
    use HandlesImageUploads;
    use HandlesTemplates;
    use InteractsWithLanguages;
    use ManagesRows;

    public $elements, $page, $pageId, $pageCategory;
    public $pageTags = [];
    public $categories, $tags;

    public $exceptionMessage;
    public $showErrorMessage = false;

    public $settingsBox;
    public $currentElement = ['name' => ''];

    public $previewMode = true;
    public $previewLanguage;

    public $pageTranslations = [];

    public $displayDate, $publishedOn;

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

    /******* Listeners *******/

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

}
