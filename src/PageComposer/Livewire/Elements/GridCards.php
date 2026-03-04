<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;
use Illuminate\Support\Arr;

class GridCards extends Component
{
    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public $target;

    public function mount()
    {
        Arr::set($this->data, 'content.headline', Arr::get($this->data, 'content.headline', ''));
        Arr::set($this->data, 'content.columns', Arr::get($this->data, 'content.columns', 3));

        if (!Arr::has($this->data, 'content.cards') || !is_array($this->data['content']['cards'])) {
            Arr::set($this->data, 'content.cards', [
                [
                    'title' => '',
                    'description' => '',
                    'linkLabel' => '',
                    'linkUrl' => '',
                    'icon' => '',
                    'imageUrl' => '',
                ],
            ]);
        }
    }

    public function addCard()
    {
        $this->data['content']['cards'][] = [
            'title' => '',
            'description' => '',
            'linkLabel' => '',
            'linkUrl' => '',
            'icon' => '',
            'imageUrl' => '',
        ];
    }

    public function removeCard(int $index)
    {
        unset($this->data['content']['cards'][$index]);
        $this->data['content']['cards'] = array_values($this->data['content']['cards']);
    }

    public function updateData()
    {
        $this->showElementInputs = false;

        $this->dispatch('elementUpdated.' . $this->target, data: $this->data, itemKey: $this->itemKey);
    }

    public function hasContent()
    {
        if (!is_array(Arr::get($this->data, 'content.cards'))) {
            return false;
        }

        foreach ($this->data['content']['cards'] as $card) {
            if (!empty(Arr::get($card, 'title')) || !empty(Arr::get($card, 'description'))) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.page-composer-elements.grid-cards');
    }
}
