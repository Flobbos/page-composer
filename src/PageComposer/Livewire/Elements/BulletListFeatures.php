<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;
use Illuminate\Support\Arr;

class BulletListFeatures extends Component
{
    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public $target;

    public function mount()
    {
        Arr::set($this->data, 'content.headline', Arr::get($this->data, 'content.headline', ''));

        if (!Arr::has($this->data, 'content.features') || !is_array($this->data['content']['features'])) {
            Arr::set($this->data, 'content.features', [
                [
                    'icon' => '',
                    'title' => '',
                    'description' => '',
                ],
            ]);
        }
    }

    public function addFeature()
    {
        $this->data['content']['features'][] = [
            'icon' => '',
            'title' => '',
            'description' => '',
        ];
    }

    public function removeFeature(int $index)
    {
        unset($this->data['content']['features'][$index]);
        $this->data['content']['features'] = array_values($this->data['content']['features']);
    }

    public function updateData()
    {
        $this->showElementInputs = false;

        $this->dispatch('elementUpdated.' . $this->target, data: $this->data, itemKey: $this->itemKey);
    }

    public function hasContent()
    {
        if (!is_array(Arr::get($this->data, 'content.features'))) {
            return false;
        }

        foreach ($this->data['content']['features'] as $feature) {
            if (!empty(Arr::get($feature, 'title')) || !empty(Arr::get($feature, 'description'))) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.page-composer-elements.bullet-list-features');
    }
}
