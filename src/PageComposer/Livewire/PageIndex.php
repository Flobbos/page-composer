<?php

namespace Flobbos\PageComposer\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Flobbos\PageComposer\Models\Page;
use Illuminate\Support\Facades\Storage;
use Flobbos\PageComposer\Models\Category;

class PageIndex extends Component
{
    use WithPagination;

    public $pages;
    public Page $currentPage;
    public $currentPageId;

    public int $perPage = 15;

    public $confirmDelete = false;
    public $showConfirmDelete = false;

    public $confirmHardDelete = false;
    public $showConfirmHardDelete = false;

    #[Url(except: false)]
    public $showTrash = false;

    public $trashedPages = 0;

    #[Url()]
    public $filter;

    public function mount()
    {
        $this->currentPage = new Page;
        $this->filter = request()->get('filter');
    }

    public function render()
    {
        if ($this->showTrash) {
            $this->pages = Page::onlyTrashed()
                ->with('translations.language')
                ->orderByDesc('id')
                ->paginate($this->perPage);
        } elseif ($this->filter) {
            $this->pages = Page::with('translations.language')
                ->where('category_id', $this->filter)
                ->orderByDesc('id')
                ->paginate($this->perPage);
        } else {
            $this->pages = Page::with('translations.language')
                ->orderByDesc('id')
                ->paginate($this->perPage);
        }
        $this->trashedPages = Page::onlyTrashed()->count();
        return view('page-composer::livewire.page-index')->with([
            'categories' => Category::all()
        ]);
    }

    public function setFilter(int $filterId)
    {
        $this->filter = $filterId;
        $this->resetPage();
    }

    public function updatedShowTrash()
    {
        $this->resetPage();
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function setActive(Page $page)
    {
        $page->is_published = !$page->is_published;
        if (!$page->published_on) {
            $page->published_on = now();
        }
        $page->save();
    }

    public function updatedConfirmDelete()
    {
        if ($this->confirmDelete) {
            $this->deletePage($this->currentPageId);
        }
    }

    public function updatedConfirmHardDelete()
    {
        if ($this->confirmHardDelete) {
            $this->hardDeletePage($this->currentPageId);
        }
    }

    public function deletePage($pageId)
    {
        if (!$this->confirmDelete) {
            $this->currentPageId = $pageId;
            $this->showConfirmDelete = true;
            return;
        }

        $page = Page::findOrFail($this->currentPageId);
        //Delete page
        $page->is_published = false;
        $page->save();
        $page->delete();
        session()->flash('message', 'Page successfully moved to trash.');
        //Reset
        $this->reset('confirmDelete', 'showConfirmDelete');
    }

    public function restorePage($id)
    {
        $page = Page::withTrashed()->findOrFail($id);
        $page->restore();
        $page->save();

        session()->flash('message', 'Page successfully restored.');

        $this->reset('showTrash');
    }

    public function hardDeletePage($id)
    {
        $page = Page::withTrashed()->findOrFail($id);

        if (!$this->confirmHardDelete) {
            $this->currentPageId = $page->id;
            $this->showConfirmHardDelete = true;
            return;
        }
        //Delete photos
        if (Storage::exists('photos/' . $page->photo)) {
            Storage::delete('photos/' . $page->photo);
        }
        if (Storage::exists('photos/' . $page->newsletter_image)) {
            Storage::delete('photos/' . $page->newsletter_image);
        }
        if (Storage::exists('photos/' . $page->slider_image)) {
            Storage::delete('photos/' . $page->slider_image);
        }
        //Delete page
        $page->forceDelete();
        session()->flash('message', 'Page permanently deleted.');
        //Reset
        $this->reset('confirmHardDelete', 'showConfirmHardDelete');
    }
}
