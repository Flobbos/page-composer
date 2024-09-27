<?php

namespace Flobbos\PageComposer\Livewire;;

use Livewire\Component;
use Flobbos\PageComposer\Models\Element;

class ElementList extends Component
{
    public $showSelector = false;
    public $comlumn_key;
    public $visible;

    public function render()
    {
        return view('page-composer::livewire.element-list')->with([
            'elements' => Element::all()
        ]);
    }
}
