<?php

namespace Flobbos\PageComposer\Livewire;

use Flobbos\PageComposer\Models\Column;
use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class RowComponent extends Component
{
    public $row;
    public $rowKey, $previewMode;
    public $source;

    public function mount()
    {
        $this->source = $this->id();
        $this->syncAvailableSpace();
    }

    public function render()
    {
        return view('page-composer::livewire.row-component');
    }

    public function columnWidth(int $size): string
    {
        $sizes = config('pagecomposer.column_widths', [
            12 => 'w-full',
            11 => 'w-11/12',
            10 => 'w-5/6',
            9 => 'w-3/4',
            8 => 'w-2/3',
            7 => 'w-7/12',
            6 => 'w-1/2',
            5 => 'w-5/12',
            4 => 'w-1/3',
            3 => 'w-1/4',
            2 => 'w-1/6',
            1 => 'w-1/12',
        ]);

        return Arr::get($sizes, $size, 'w-full');
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

        $this->syncAvailableSpace();

        $this->dispatch('columnUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    #[Computed]
    public function availableSpace(): int
    {
        return max(0, 12 - (int) collect(Arr::get($this->row, 'columns', []))
            ->sum(fn($column) => (int) Arr::get($column, 'column_size', 0)));
    }

    #[Computed]
    public function columnPresets()
    {
        $defaultPresets = [
            [
                'size' => 12,
                'label' => __('Full'),
                'preview_segments' => 1,
                'group' => 'full',
                'requires_empty' => true,
            ],
            [
                'size' => 6,
                'label' => __('Half'),
                'preview_segments' => 2,
                'group' => 'halves_quarters',
            ],
            [
                'size' => 4,
                'label' => '1/3',
                'preview_segments' => 3,
                'group' => 'thirds',
            ],
            [
                'size' => 3,
                'label' => '1/4',
                'preview_segments' => 4,
                'group' => 'halves_quarters',
            ],
        ];

        $configuredPresets = config('pagecomposer.column_presets', []);
        if (!is_array($configuredPresets)) {
            $configuredPresets = [];
        }

        $merged = collect($defaultPresets)
            ->keyBy(fn(array $preset) => (int) $preset['size']);

        foreach ($configuredPresets as $preset) {
            if (!is_array($preset)) {
                continue;
            }

            $size = (int) Arr::get($preset, 'size', 0);
            if ($size < 1 || $size > 12) {
                continue;
            }

            $existingPreset = $merged->get($size, []);
            $merged->put($size, array_merge($existingPreset, [
                'size' => $size,
                'label' => (string) Arr::get($preset, 'label', Arr::get($existingPreset, 'label', (string) $size . '/12')),
                'preview_segments' => max(1, (int) Arr::get($preset, 'preview_segments', Arr::get($existingPreset, 'preview_segments', 1))),
                'group' => Arr::get($preset, 'group', Arr::get($existingPreset, 'group')),
                'requires_empty' => (bool) Arr::get($preset, 'requires_empty', Arr::get($existingPreset, 'requires_empty', false)),
            ]));
        }

        return $merged
            ->values()
            ->sortByDesc(fn(array $preset) => $preset['size'])
            ->values()
            ->all();
    }

    #[Computed]
    public function availableColumnPresets()
    {
        $availableSpace = $this->availableSpace();
        $columns = Arr::get($this->row, 'columns', []);
        $hasColumns = count($columns) > 0;

        $sizeToGroup = collect($this->columnPresets)
            ->mapWithKeys(fn(array $preset) => [(int) $preset['size'] => Arr::get($preset, 'group')])
            ->all();

        $usedGroups = collect($columns)
            ->map(function ($column) use ($sizeToGroup) {
                $columnSize = (int) Arr::get($column, 'column_size');
                return $sizeToGroup[$columnSize] ?? null;
            })
            ->filter(fn($group) => filled($group))
            ->unique()
            ->values();

        return collect($this->columnPresets)
            ->filter(function (array $preset) use ($availableSpace, $hasColumns, $usedGroups) {
                $size = (int) Arr::get($preset, 'size', 0);
                if ($size <= 0 || $size > $availableSpace) {
                    return false;
                }

                if ((bool) Arr::get($preset, 'requires_empty', false) && $hasColumns) {
                    return false;
                }

                $group = Arr::get($preset, 'group');
                if ($usedGroups->isNotEmpty() && filled($group) && !$usedGroups->contains($group)) {
                    return false;
                }

                return true;
            })
            ->values()
            ->all();
    }

    public function deleteColumn($columnKey)
    {
        if (isset($this->row['columns'][$columnKey]['id'])) {
            if ($column = Column::find($this->row['columns'][$columnKey]['id'])) {
                $column->delete();
            }
        }
        $size = $this->row['columns'][$columnKey]['column_size'];
        unset($this->row['columns'][$columnKey]);
        $this->row['columns'] = array_values($this->row['columns']);
        $this->syncAvailableSpace();

        $this->dispatch('columnUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    #[On('itemsUpdated.{source}')]
    public function itemsUpdated(array $column, int $columnKey)
    {
        $this->row['columns'][$columnKey] = $column;

        $this->dispatch('columnUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    #[Computed]
    public function sortedColumns()
    {
        return Arr::sort($this->row['columns'], function ($value) {
            return $value['sorting'];
        });
    }

    /**
     * Handle a column reorder from Livewire 4's wire:sort directive.
     *
     * @param string|int $id       column array key (from wire:sort:item)
     * @param int        $position zero-based target position
     */
    public function updateColumnOrder($id, $position)
    {
        $columns = Arr::get($this->row, 'columns', []);
        if (empty($columns)) {
            return;
        }

        $sourceIndex = $id;
        if (!array_key_exists($sourceIndex, $columns)) {
            return;
        }

        $orderedIndices = collect($columns)
            ->map(fn($col, $index) => ['index' => $index, 'sorting' => (int) Arr::get($col, 'sorting', 0)])
            ->sortBy('sorting')
            ->pluck('index')
            ->values()
            ->all();

        $currentPosition = array_search($sourceIndex, $orderedIndices, true);
        if ($currentPosition === false) {
            return;
        }

        array_splice($orderedIndices, $currentPosition, 1);
        array_splice($orderedIndices, max(0, (int) $position), 0, $sourceIndex);

        foreach ($orderedIndices as $newPosition => $colIndex) {
            $this->row['columns'][$colIndex]['sorting'] = $newPosition + 1;
        }

        $this->dispatch('columnUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    public function saveRowSettings()
    {
        $this->syncAvailableSpace();
        $this->dispatch('rowUpdated', row: $this->row, rowKey: $this->rowKey);
    }

    private function syncAvailableSpace(): void
    {
        $this->row['available_space'] = $this->availableSpace();
    }
}
