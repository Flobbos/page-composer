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

    #[Url(as: 'q')]
    public $search = '';

    public function mount()
    {
        $this->currentPage = new Page;
        $this->filter = request()->get('filter');
        $this->search = request()->get('q', '');
    }

    public function render()
    {
        $query = Page::with('translations.language');

        if ($this->showTrash) {
            $query->onlyTrashed();
        } elseif ($this->filter) {
            $query->where('category_id', $this->filter);
        }

        // Add search functionality
        if (!empty($this->search)) {
            $search = trim($this->search);
            $query->where(function($q) use ($search) {
                // Search by page ID
                $q->where('id', 'like', '%' . $search . '%')
                  // Search in translations
                  ->orWhereHas('translations', function($query) use ($search) {
                      $query->where('slug', 'like', '%' . $search . '%')
                            ->orWhere('content->name', 'like', '%' . $search . '%')
                            ->orWhere('content->title', 'like', '%' . $search . '%');
                  });
            });
        }

        $pages = $query->orderByDesc('id')->paginate($this->perPage);
        
        $this->trashedPages = Page::onlyTrashed()->count();
        return view('page-composer::livewire.page-index')->with([
            'pages' => $pages,
            'categories' => Category::all()
        ]);
    }

    public function setFilter(int $filterId)
    {
        $this->filter = $filterId;
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->reset('filter');
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

    public function updatedSearch()
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
