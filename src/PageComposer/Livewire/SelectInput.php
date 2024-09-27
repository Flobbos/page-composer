<?php

namespace Flobbos\PageComposer\Livewire;;

use Livewire\Component;

class SelectInput extends Component
{

    public $selected;
    public $options = [];
    public $placeholder = '';
    public $name = '';

    public $labelBy = 'label';
    public $trackBy = 'id';

    public $open = false;

    public function render()
    {
        return view('page-composer::livewire.select-input');
    }

    public function selectOption($option)
    {
        $this->selected = $option;
        $this->dispatch('categorySelected', $option);
        $this->open = false;
    }
}
