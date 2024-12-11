<?php

namespace Flobbos\PageComposer\Livewire;

use Flobbos\PageComposer\Models\Column;
use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;

class RowComponent extends Component
{
    public $row;
    public $rowKey, $previewMode;
    public $source;

    public function mount()
    {
        $this->source = $this->id();
    }

    public function render()
    {
        return view('page-composer::livewire.row-component')->with([
            'source' => $this->id()
        ]);
    }

    public function columnWidth(int $size)
    {
        $sizes = [
            '12' => 'w-full',
            '11' => 'w-11/12',
            '10' => 'w-5/6',
            '9' => 'w-3/4',
            '8' => 'w-2/3',
            '7' => 'w-7/12',
            '6' => 'w-1/2',
            '5' => 'w-5/12',
            '4' => 'w-1/3',
            '3' => 'w-1/4',
            '2' => 'w-1/6',
            '1' => 'w-1/12',
        ];

        return $sizes[$size];
    }

    public function addColumn(int $size)
    {
        $this->row['columns'][] = [
            'column_items' => [],
            'column_size' => $size,
            'attributes' => [],
            'sorting' => $this->row['columns'] ? count($this->row['columns']) : 1,
            'active' => true,
        ];

        $this->row['available_space'] -= $size;

        $this->dispatch('columnUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    public function deleteColumn($columnKey)
    {
        if (isset($this->row['columns'][$columnKey]['id'])) {
            if ($column = Column::find($this->row['columns'][$columnKey]['id'])) {
                $column->delete();
            }
        }
        $size = $this->row['columns'][$columnKey]['column_size'];
        $this->row['available_space'] += $size;
        unset($this->row['columns'][$columnKey]);
        $this->row['columns'] = array_values($this->row['columns']);

        $this->dispatch('columnUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    #[On('itemsUpdated.{source}')]
    public function itemsUpdated(array $column, int $columnKey)
    {
        $this->row['columns'][$columnKey] = $column;

        $this->dispatch('columnUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    public function getSortedColumnsProperty()
    {
        return Arr::sort($this->row['columns'], function ($value) {
            return $value['sorting'];
        });
    }

    public function updateColumnOrder($columns)
    {
        foreach ($columns as $col) {
            $this->row['columns'][$col['value']]['sorting'] = $col['order'];
        }

        $this->row['columns'] = array_values($this->row['columns']);

        $this->dispatch('columnUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    public function saveRowSettings()
    {
        $this->dispatch('rowUpdated', row: $this->row, rowKey: $this->rowKey);
    }
}
