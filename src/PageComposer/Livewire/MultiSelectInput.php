<?php

namespace Flobbos\PageComposer\Livewire;;

use Livewire\Component;
use Illuminate\Support\Collection;

class MultiSelectInput extends Component
{
    public $selected;
    public $options;
    public $availableOptions;
    public $placeholder = '';
    public $name = '';
    public $eventName = 'optionsUpdated';

    public $labelBy = 'label';
    public $trackBy = 'id';

    public $open = false;

    public function mount()
    {
        if (is_null($this->selected)) {
            $this->selected = collect();
        } else {
            $this->selected = collect($this->selected);
        }
        $this->filterAvailable();
    }

    public function render()
    {
        return view('livewire.multi-select-input');
    }

    public function selectOption($option)
    {
        $this->selected->push($option);
        $this->availableOptions = $this->availableOptions->filter(function ($item) use ($option) {
            return $item['id'] !== $option['id'];
        });
        $this->open = false;
        $this->emitUp($this->eventName, $this->selected);
    }

    public function removeOption($option)
    {
        foreach ($this->selected as $key => $selected) {
            if ($option['id'] === $selected['id']) {
                $this->selected->forget($key);
            }
        }
        $this->availableOptions->push($option);
        $this->emitUp($this->eventName, $this->selected);
    }

    public function filterAvailable()
    {
        $selected = $this->selected->pluck('id')->all();
        $this->availableOptions = collect($this->options->toArray());
        foreach ($this->availableOptions as $key => $option) {
            if (in_array($option['id'], $selected)) {
                $this->availableOptions->forget($key);
            }
        }
    }
}
