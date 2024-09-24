<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BaseElement extends Component
{
    public $elementData;

    public $itemKey;

    public $showElementInputs = false;

    public $sorting = [];

    public $previewMode = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(array $data, $itemKey, bool $showElementInputs, array $sorting, bool $previewMode = false)
    {
        $this->elementData = $data;
        $this->itemKey = $itemKey;
        $this->showElementInputs = $showElementInputs;
        $this->sorting = $sorting;
        $this->previewMode = $previewMode;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.base-element');
    }
}
