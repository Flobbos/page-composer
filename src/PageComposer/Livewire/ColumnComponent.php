<?php

namespace Flobbos\PageComposer\Livewire;

use Flobbos\PageComposer\Models\Element;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;

class ColumnComponent extends Component
{
    /**
     * Bound to the parent's $row['columns'][columnKey] via wire:model.
     * Mutations propagate up automatically; no dispatch chain needed.
     */
    #[Modelable]
    public $column;

    public $columnKey;
    public $previewMode;

    /**
     * Used as the dispatch suffix for events emitted by published
     * page-composer-elements.* components (elementAdded.{source} /
     * elementUpdated.{source}). Generated per-instance.
     */
    public $source;

    public function mount()
    {
        $this->source = $this->id();
    }

    public function render()
    {
        return view('page-composer::livewire.column-component');
    }

    public function saveColumnSettings()
    {
        // No-op now that Modelable propagates mutations to the parent.
        // Kept so the existing wire:click in the column-settings panel
        // still resolves.
    }

    #[On('elementAdded.{source}')]
    public function elementAdded(Element $element)
    {
        $this->column['column_items'][] = $this->generateElement($element);
    }

    #[On('elementUpdated.{source}')]
    public function elementUpdated(array $data, int $itemKey)
    {
        $this->column['column_items'][$itemKey] = $data;
    }

    /**
     * Stage an element removal in component state. The column item is
     * only removed from the DB when the page is saved, via PageBuilder's
     * orphan purge. A refresh before save therefore restores the element.
     */
    public function deleteElement(int $itemKey)
    {
        unset($this->column['column_items'][$itemKey]);
        unset($this->sortedElements);

        //Resort elements
        $count = 1;
        foreach ($this->sortedElements as $key => $item) {
            $this->column['column_items'][$key]['sorting'] = $count;
            $count++;
        }

        unset($this->sortedElements);
    }

    #[Computed]
    public function sortedElements()
    {
        return Arr::sort($this->column['column_items'], function ($value) {
            return $value['sorting'];
        });
    }

    public function getElementPositionArray($itemKey)
    {
        $position = [
            'up' => false,
            'down' => false,
            'position' => 1
        ];

        if ($count = count($this->column['column_items'])) {
            $position = [
                'up' => $this->column['column_items'][$itemKey]['sorting'] > 1,
                'down' => $this->column['column_items'][$itemKey]['sorting'] < count($this->column['column_items']),
                'position' => $this->column['column_items'][$itemKey]['sorting']
            ];
        }
        return $position;
    }

    public function sortElementDown($itemKey)
    {
        $sortedElements = $this->sortedElements;
        while (key($sortedElements) !== $itemKey) next($sortedElements);
        $current = current($sortedElements);
        $this->column['column_items'][$itemKey]['sorting'] = $current['sorting'] + 1;
        if ($next = next($sortedElements)) {
            $this->column['column_items'][key($sortedElements)]['sorting'] = $next['sorting'] - 1;
        }
        unset($this->sortedElements);
    }

    public function sortElementUp($itemKey)
    {
        $sortedElements = $this->sortedElements;
        while (key($sortedElements) !== $itemKey) next($sortedElements);
        $current = current($sortedElements);
        $this->column['column_items'][$itemKey]['sorting'] = $current['sorting'] - 1;
        if ($prev = prev($sortedElements)) {
            $this->column['column_items'][key($sortedElements)]['sorting'] = $prev['sorting'] + 1;
        }
        unset($this->sortedElements);
    }

    public function generateElement($element)
    {
        return [
            'element_id' => $element->id,
            'name' => $element->name,
            'icon' => $element->icon,
            'component' => $element->component,
            'attributes' => [],
            'sorting' => count($this->column['column_items']) + 1,
            'active' => true,
            'content' => []
        ];
    }
}
