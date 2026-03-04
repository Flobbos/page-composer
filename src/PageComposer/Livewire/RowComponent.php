<?php

namespace Flobbos\PageComposer\Livewire;

use Flobbos\PageComposer\Models\Column;
use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;

class RowComponent extends Component
{
    public $row;
    public $rowKey, $previewMode;

    public function render()
    {
        return view('page-composer::livewire.row-component');
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
    }

    #[Computed]
    public function sortedColumns()
    {
        return Arr::sort($this->row['columns'], function ($value) {
            return $value['sorting'];
        });
    }

    public function getSortedColumnsProperty()
    {
        return $this->sortedColumns();
    }

    public function updateColumnOrder($columns)
    {
        foreach ($columns as $col) {
            $this->row['columns'][$col['value']]['sorting'] = $col['order'];
        }

        $this->row['columns'] = array_values($this->row['columns']);
    }

    public function saveRowSettings()
    {
    }
}
