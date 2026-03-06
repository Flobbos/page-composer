<?php

namespace App\Livewire\PageComposerElements;

use Livewire\Component;
use Illuminate\Support\Arr;

class TestimonialsTrustBadges extends Component
{
    public $data, $itemKey, $sorting, $previewMode;

    public $showElementInputs = false;

    public $target;

    public function mount()
    {
        Arr::set($this->data, 'content.headline', Arr::get($this->data, 'content.headline', ''));

        if (!Arr::has($this->data, 'content.testimonials') || !is_array($this->data['content']['testimonials'])) {
            Arr::set($this->data, 'content.testimonials', [
                [
                    'quote' => '',
                    'name' => '',
                    'role' => '',
                ],
            ]);
        }

        if (!Arr::has($this->data, 'content.badges') || !is_array($this->data['content']['badges'])) {
            Arr::set($this->data, 'content.badges', [
                [
                    'label' => '',
                    'value' => '',
                ],
            ]);
        }
    }

    public function addTestimonial()
    {
        $this->data['content']['testimonials'][] = [
            'quote' => '',
            'name' => '',
            'role' => '',
        ];
    }

    public function removeTestimonial(int $index)
    {
        unset($this->data['content']['testimonials'][$index]);
        $this->data['content']['testimonials'] = array_values($this->data['content']['testimonials']);
    }

    public function addBadge()
    {
        $this->data['content']['badges'][] = [
            'label' => '',
            'value' => '',
        ];
    }

    public function removeBadge(int $index)
    {
        unset($this->data['content']['badges'][$index]);
        $this->data['content']['badges'] = array_values($this->data['content']['badges']);
    }

    public function updateData()
    {
        $this->showElementInputs = false;

        $this->dispatch('elementUpdated.' . $this->target, data: $this->data, itemKey: $this->itemKey);
    }

    public function hasContent()
    {
        foreach (Arr::get($this->data, 'content.testimonials', []) as $testimonial) {
            if (!empty(Arr::get($testimonial, 'quote'))) {
                return true;
            }
        }

        foreach (Arr::get($this->data, 'content.badges', []) as $badge) {
            if (!empty(Arr::get($badge, 'label')) || !empty(Arr::get($badge, 'value'))) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.page-composer-elements.testimonials-trust-badges');
    }
}
