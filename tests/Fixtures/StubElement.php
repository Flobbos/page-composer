<?php

namespace Flobbos\PageComposer\Tests\Fixtures;

use Livewire\Component;

/**
 * Stand-in for the user-published page-composer-elements.* Livewire
 * components. Registered under each element name we use in tests so the
 * orchestrator's Blade view can render rows that contain column items.
 */
class StubElement extends Component
{
    public $data = [];
    public $itemKey;
    public $sorting;
    public $previewMode = false;
    public $target;

    public function render()
    {
        return '<div data-stub-element></div>';
    }
}
