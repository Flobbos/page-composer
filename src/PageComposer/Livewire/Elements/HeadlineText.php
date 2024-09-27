<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;

class HeadlineText extends Component
{
    public $data, $itemKey, $editor, $sorting, $previewMode;

    public $showElementInputs = false;

    protected $listeners = [
        'showEditMode'
    ];

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
        return view('livewire.page-composer-elements.headline-text');
    }
}
