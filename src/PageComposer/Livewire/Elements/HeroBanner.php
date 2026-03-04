<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;
use Illuminate\Support\Arr;

class HeroBanner extends Component
{
    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public $target;

    public function mount()
    {
        Arr::set($this->data, 'content.bgImageUrl', Arr::get($this->data, 'content.bgImageUrl', ''));
        Arr::set($this->data, 'content.headline', Arr::get($this->data, 'content.headline', ''));
        Arr::set($this->data, 'content.subheadline', Arr::get($this->data, 'content.subheadline', ''));
        Arr::set($this->data, 'content.ctaLabel', Arr::get($this->data, 'content.ctaLabel', ''));
        Arr::set($this->data, 'content.ctaUrl', Arr::get($this->data, 'content.ctaUrl', ''));
        Arr::set($this->data, 'content.ctaTarget', Arr::get($this->data, 'content.ctaTarget', '_self'));
        Arr::set($this->data, 'content.overlayOpacity', Arr::get($this->data, 'content.overlayOpacity', 50));
        Arr::set($this->data, 'content.minHeight', Arr::get($this->data, 'content.minHeight', 'h-96'));
    }

    public function updateData()
    {
        $this->showElementInputs = false;

        $this->dispatch('elementUpdated.' . $this->target, data: $this->data, itemKey: $this->itemKey);
    }

    public function hasContent()
    {
        return !empty(Arr::get($this->data, 'content.headline'))
            || !empty(Arr::get($this->data, 'content.subheadline'))
            || !empty(Arr::get($this->data, 'content.bgImageUrl'));
    }

    public function render()
    {
        return view('livewire.page-composer-elements.hero-banner');
    }
}
