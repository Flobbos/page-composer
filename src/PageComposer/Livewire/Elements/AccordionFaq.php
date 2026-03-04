<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;
use Illuminate\Support\Arr;

class AccordionFaq extends Component
{
    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public function mount()
    {
        Arr::set($this->data, 'content.headline', Arr::get($this->data, 'content.headline', ''));

        if (!Arr::has($this->data, 'content.items') || !is_array($this->data['content']['items'])) {
            Arr::set($this->data, 'content.items', [
                [
                    'question' => '',
                    'answer' => '',
                ],
            ]);
        }
    }

    public function addItem()
    {
        $this->data['content']['items'][] = [
            'question' => '',
            'answer' => '',
        ];
    }

    public function removeItem(int $index)
    {
        unset($this->data['content']['items'][$index]);
        $this->data['content']['items'] = array_values($this->data['content']['items']);
    }

    public function updateData()
    {
        $this->showElementInputs = false;
    }

    public function hasContent()
    {
        foreach (Arr::get($this->data, 'content.items', []) as $item) {
            if (!empty(Arr::get($item, 'question')) || !empty(Arr::get($item, 'answer'))) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.page-composer-elements.accordion-faq');
    }
}
