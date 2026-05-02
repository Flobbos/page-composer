<?php

namespace Flobbos\PageComposer\Livewire\Concerns;

use Flobbos\PageComposer\Models\Row;
use Flobbos\PageComposer\Services\SortService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

/**
 * @property \Flobbos\PageComposer\Models\Language|null $currentLanguage
 */
trait ManagesRows
{
    public array $rows = [];
    public bool $showMiniMap = false;

    public function addRow(): void
    {
        $this->rows[$this->currentLanguage->locale]['rows'][] = [
            'uuid' => (string) Str::uuid(),
            'columns' => [],
            'attributes' => [],
            'alignment' => 'center',
            'expanded' => false,
            'active' => true,
            'sorting' => $this->rows[$this->currentLanguage->locale]['rows'] ? count($this->rows[$this->currentLanguage->locale]['rows']) + 1 : 1,
            'available_space' => 12
        ];
    }

    /**
     * Update the sorting for the rows. Called by Livewire 4's wire:sort when
     * a row is dropped in a new position in the mini map.
     *
     * @param string|int $id       sortable key from wire:sort:item (uuid, db id, or tmp-N)
     * @param int        $position zero-based target position
     */
    public function updateRowSorting($id, $position): void
    {
        $locale = $this->currentLanguage->locale ?? null;
        if (!$locale) {
            return;
        }

        $rows = $this->rows[$locale]['rows'] ?? [];

        $this->rows[$locale]['rows'] = app(SortService::class)->reorder(
            $rows,
            fn(array $row, $key) => $this->rowSortableKey($row, (int) $key),
            $id,
            (int) $position,
        );
    }

    public function rowSortableKey(array $row, int $fallbackIndex): string
    {
        if (filled(Arr::get($row, 'id'))) {
            return (string) Arr::get($row, 'id');
        }

        if (filled(Arr::get($row, 'uuid'))) {
            return (string) Arr::get($row, 'uuid');
        }

        return 'tmp-' . $fallbackIndex;
    }

    #[Computed]
    public function sortedRows(): array
    {
        if (!isset($this->currentLanguage)) {
            return [];
        }

        if (empty($this->rows[$this->currentLanguage->locale]['rows'])) {
            return [];
        }

        return Arr::sort($this->rows[$this->currentLanguage->locale]['rows'], function ($value) {
            return $value['sorting'];
        });
    }

    public function sortItems(array $items): array
    {
        return Arr::sort($items, function ($value) {
            return $value['sorting'];
        });
    }

    #[On('deleteRow')]
    public function deleteRow(string $rowKey): void
    {
        if (isset($this->rows[$this->currentLanguage->locale]['rows'][$rowKey]['id'])) {
            if ($row = Row::find($this->rows[$this->currentLanguage->locale]['rows'][$rowKey]['id'])) {
                $row->delete();
            }
        }
        unset($this->rows[$this->currentLanguage->locale]['rows'][$rowKey]);
    }

    protected function ensureUnsavedRowsHaveUuid(string $locale): void
    {
        foreach (Arr::get($this->rows, $locale . '.rows', []) as $rowKey => $row) {
            $this->rows[$locale]['rows'][$rowKey]['available_space'] = $this->calculateRowAvailableSpace(Arr::get($row, 'columns', []));

            if (filled(Arr::get($row, 'id')) || filled(Arr::get($row, 'uuid'))) {
                continue;
            }

            $this->rows[$locale]['rows'][$rowKey]['uuid'] = (string) Str::uuid();
        }
    }

    protected function calculateRowAvailableSpace(array $columns): int
    {
        return max(0, 12 - (int) collect($columns)
            ->sum(fn($column) => (int) Arr::get($column, 'column_size', 0)));
    }
}
