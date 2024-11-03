<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;

class YouTube extends Component
{
    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public $target;

    protected $rules = [
        'data.content.videoUrl' => 'required|url'
    ];

    protected $messages = [
        'data.content.videoUrl.required' => 'Please enter a valid YouTube embed link',
        'data.content.videoUrl.url' => 'The URL you provided is not valid'
    ];

    public function updatedData()
    {
        $this->validate();
    }

    public function updateData()
    {
        $this->validate();

        $this->showElementInputs = false;

        $this->dispatch('elementUpdated.' . $this->target, data: $this->data, itemKey: $this->itemKey);
    }

    public function hasContent()
    {
        return !empty($this->data['content']);
    }

    public function render()
    {
        return view('livewire.page-composer-elements.you-tube');
    }
}
