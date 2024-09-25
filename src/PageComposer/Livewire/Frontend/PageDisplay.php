<?php

namespace Flobbos\PageComposer\Livewire\Frontend;

use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTranslation;
use Livewire\Component;

class PageDisplay extends Component
{
    public $slug;

    public function mount($slug)
    {
        $this->slug = $slug;
    }
    public function render()
    {
        // Get page ID from slug
        $pageTranslation = PageTranslation::where('slug', $this->slug)->select('id', 'slug', 'language_id', 'page_id')->firstOrFail();

        // Get actual page content
        $page = Page::with(['translation' => function ($q) use ($pageTranslation) {
            $q->where('language_id', $pageTranslation->language_id);
        }, 'rows' => function ($q) use ($pageTranslation) {
            $q->where('language_id', $pageTranslation->language_id)->with('columns.column_items.element');
        }])->where('is_published', true)->where('published_on', '<=', now())->findOrFail($pageTranslation->page_id);

        return view('livewire.frontend.page-display')
            ->with([
                'page' => $page
            ])->layout('layouts.frontend');
    }
}
