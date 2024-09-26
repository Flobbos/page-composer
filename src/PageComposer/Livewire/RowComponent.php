<?php

namespace Flobbos\PageComposer\Livewire;;

use Flobbos\PageComposer\Models\Column;
use Livewire\Component;
use Illuminate\Support\Arr;

class RowComponent extends Component
{
    public $row, $rowKey, $previewMode;

    public $listeners = ['deleteColumn', 'itemsUpdated'];

    public function saveRowSettings()
    {
        $this->emitUp('rowUpdated', $this->row, $this->rowKey);
    }

    public function columnWidth(int $size)
    {
        $sizes = [
            '12' => 'w-full',
            '6' => 'w-1/2',
            '3' => 'w-1/4'
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

        $this->emitUp('columnUpdated', $this->row, $this->rowKey);
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

        $this->emitUp('columnUpdated', $this->row, $this->rowKey);
    }

    public function itemsUpdated(array $column, int $columnKey)
    {
        $this->row['columns'][$columnKey] = $column;

        $this->emitUp('columnUpdated', $this->row, $this->rowKey);
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

        $this->emitUp('columnUpdated', $this->row, $this->rowKey);
    }

    public function render()
    {
        return view('page-composer::livewire.row-component');
    }
}
