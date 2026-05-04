<?php

namespace Flobbos\PageComposer\Tests\Fixtures;

use Livewire\Component;

/**
 * Minimal placeholder for package-composer-elements.* Livewire components.
 * The real element classes live under App\Livewire\PageComposerElements once
 * published to the host app; in the package test suite nothing publishes them,
 * so we register this stub as a catch-all for rendering.
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
        return <<<'BLADE'
            <div data-stub-element></div>
        BLADE;
    }
}
