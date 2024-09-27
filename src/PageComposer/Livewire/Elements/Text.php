<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;

class Text extends Component
{
    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public function updateData()
    {
        $this->showElementInputs = false;

        $this->dispatch('elementUpdated', $this->data, $this->itemKey);
    }

    public function hasContent()
    {
        return !empty($this->data['content']);
    }

    public function render()
    {
        return view('livewire.page-composer-elements.text');
    }
}
