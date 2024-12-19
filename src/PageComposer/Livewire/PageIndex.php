<?php

namespace Flobbos\PageComposer\Livewire;

use Flobbos\PageComposer\Models\Page;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class PageIndex extends Component
{
    public $pages;
    public Page $currentPage;

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
    }

    public function render()
    {
        if ($this->showTrash) {
            $this->pages = Page::onlyTrashed()->with('translations')->get();
        } elseif ($this->filter) {
            $this->pages = Page::with('translations')->where('category_id', $this->filter)->get();
        } else {
            $this->pages = Page::with('translations')->get();
        }
        $this->trashedPages = Page::onlyTrashed()->count();
        return view('page-composer::livewire.page-index')->with([
            'categories' => Category::all()
        ]);
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
            $this->deletePage($this->currentPage);
        }
    }

    public function updatedConfirmHardDelete()
    {
        if ($this->confirmHardDelete) {
            $this->hardDeletePage($this->currentPage->id);
        }
    }

    public function deletePage(Page $page)
    {
        if (!$this->confirmDelete) {
            $this->currentPage = $page;
            $this->showConfirmDelete = true;
            return;
        }
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

        session()->flash('message', 'Page successfully moved to trash.');

        $this->reset('showTrash');
    }

    public function hardDeletePage($id)
    {
        $page = Page::withTrashed()->findOrFail($id);

        if (!$this->confirmHardDelete) {
            $this->currentPage = $page;
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
