<?php

namespace Flobbos\PageComposer\Livewire;;

use Flobbos\PageComposer\Models\Element;
use Livewire\Component;
use Flobbos\PageComposer\Models\ColumnItem;
use Illuminate\Support\Arr;

class ColumnComponent extends Component
{
    public $column, $columnKey, $previewMode;

    public $showColumnSettings = false;

    public $listeners = ['elementAdded', 'elementUpdated', 'deleteElement', 'sortElementUp', 'sortElementDown'];

    public function saveColumnSettings()
    {
        $this->dispatch('itemsUpdated', column: $this->column, columnKey: $this->columnKey);
    }

    public function elementAdded(Element $element)
    {
        $this->column['column_items'][] = $this->generateElement($element);

        $this->dispatch('itemsUpdated', column: $this->column, columnKey: $this->columnKey);
    }

    public function elementUpdated(array $data, int $itemKey)
    {
        $this->column['column_items'][$itemKey] = $data;

        $this->dispatch('itemsUpdated', column: $this->column, columnKey: $this->columnKey);
    }

    public function deleteElement(int $itemKey)
    {
        if (isset($this->column['column_items'][$itemKey]['id'])) {
            if ($columnItem = ColumnItem::find($this->column['column_items'][$itemKey]['id'])) {
                $columnItem->delete();
            }
        }
        unset($this->column['column_items'][$itemKey]);

        //Resort elements
        $count = 1;
        foreach ($this->getSortedElementsProperty() as $key => $item) {
            $this->column['column_items'][$key]['sorting'] = $count;
            $count++;
        }

        $this->dispatch('itemsUpdated', column: $this->column, columnKey: $this->columnKey);
    }

    public function getSortedElementsProperty()
    {
        return Arr::sort($this->column['column_items'], function ($value) {
            return $value['sorting'];
        });
    }

    public function getElementPositionArray($itemKey)
    {
        //Disabled positioning array
        $position = [
            'up' => false,
            'down' => false,
            'position' => 1
        ];
        //more than one element enable positioning
        if ($count = count($this->column['column_items'])) {
            $position = [
                'up' => $this->column['column_items'][$itemKey]['sorting'] > 1 ? true : false,
                'down' => $this->column['column_items'][$itemKey]['sorting'] < count($this->column['column_items']) ? true : false,
                'position' => $this->column['column_items'][$itemKey]['sorting']
            ];
        }
        return $position;
    }

    public function sortElementDown($itemKey)
    {
        $sortedElements = $this->getSortedElementsProperty();
        //Advance array to correct position
        while (key($sortedElements) !== $itemKey) next($sortedElements);
        //Set the values at the current position
        $current = current($sortedElements);
        $this->column['column_items'][$itemKey]['sorting'] = $current['sorting'] + 1;
        //Set the value for the next element
        if ($next = next($sortedElements)) {
            $this->column['column_items'][key($sortedElements)]['sorting'] = $next['sorting'] - 1;
        }
        //Emit the change
        $this->dispatch('itemsUpdated', column: $this->column, columnKey: $this->columnKey);
    }

    public function sortElementUp($itemKey)
    {
        $sortedElements = $this->getSortedElementsProperty();
        //Advance array to correct position
        while (key($sortedElements) !== $itemKey) next($sortedElements);
        //Set the values at the current position
        $current = current($sortedElements);
        $this->column['column_items'][$itemKey]['sorting'] =  $current['sorting'] - 1;
        //Set the value for the previous element
        if ($prev = prev($sortedElements)) {
            $this->column['column_items'][key($sortedElements)]['sorting'] = $prev['sorting'] + 1;
        }
        //Emit the change
        $this->dispatch('itemsUpdated', column: $this->column, columnKey: $this->columnKey);
    }

    private function generateElement($element)
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

    public function render()
    {
        return view('page-composer::livewire.column-component');
    }
}
