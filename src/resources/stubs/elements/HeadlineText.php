<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;
use Livewire\Attributes\On;

class HeadlineText extends Component
{
    public $data, $itemKey, $editor, $sorting, $previewMode;

    public $showElementInputs = false;

    public $target;

    #[On('showEditMode')]
    public function showEditMode()
    {
        $this->showElementInputs = true;
    }

    public function updateData()
    {
        $this->showElementInputs = false;

        $this->dispatch('elementUpdated.' . $this->target, data: $this->data, itemKey: $this->itemKey);
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
