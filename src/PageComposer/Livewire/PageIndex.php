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

    public $currentPageId;
    public string $currentPageName = '';

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

        // Search only kicks in at 4+ characters, or for a numeric page id
        $search = trim((string) $this->search);

        if (ctype_digit($search)) {
            $query->whereKey((int) $search);
        } elseif (mb_strlen($search) >= 4) {
            $query->where(function ($q) use ($search) {
                // Search by internal page name
                $q->where('name', 'like', '%' . $search . '%')
                    // Search in translations
                    ->orWhereHas('translations', function ($query) use ($search) {
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
        $search = trim((string) $this->search);
        // Only reset pagination when the search actually applies
        if (mb_strlen($search) === 0 || mb_strlen($search) >= 4 || ctype_digit($search)) {
            $this->resetPage();
        }
        return;
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
            $this->currentPageName = (string) Page::find($pageId)?->name;
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
            $this->currentPageName = (string) $page->name;
            $this->showConfirmHardDelete = true;
            return;
        }
        //Delete photos. Uploads store their path relative to the public disk.
        foreach (['photo', 'newsletter_image', 'slider_image'] as $field) {
            if (filled($page->{$field})) {
                Storage::disk('public')->delete($page->{$field});
            }
        }
        //Delete page
        $page->forceDelete();
        session()->flash('message', 'Page permanently deleted.');
        //Reset
        $this->reset('confirmHardDelete', 'showConfirmHardDelete', 'currentPageName');
    }
}
