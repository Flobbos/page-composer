<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;
use Illuminate\Support\Arr;

class CallToActionSection extends Component
{
    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public $target;

    public function mount()
    {
        Arr::set($this->data, 'content.headline', Arr::get($this->data, 'content.headline', ''));
        Arr::set($this->data, 'content.subheadline', Arr::get($this->data, 'content.subheadline', ''));
        Arr::set($this->data, 'content.buttonLabel', Arr::get($this->data, 'content.buttonLabel', ''));
        Arr::set($this->data, 'content.buttonUrl', Arr::get($this->data, 'content.buttonUrl', ''));
        Arr::set($this->data, 'content.buttonTarget', Arr::get($this->data, 'content.buttonTarget', '_self'));
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
            || !empty(Arr::get($this->data, 'content.buttonLabel'));
    }

    public function render()
    {
        return view('livewire.page-composer-elements.call-to-action-section');
    }
}
