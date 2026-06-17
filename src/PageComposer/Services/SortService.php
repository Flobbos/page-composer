<?php

namespace Flobbos\PageComposer\Services;

use Illuminate\Support\Arr;

class SortService
{
    /**
     * Move the item identified by $sourceKey to the zero-based
     * $position and rewrite each item's `sorting` key to a 1-based
     * contiguous sequence reflecting the new order. Original array
     * keys are preserved.
     *
     * Returns the input untouched if $items is empty or $sourceKey
     * cannot be resolved, so callers can detect a no-op via ===.
     *
     * @param callable $keyResolver fn(mixed $item, mixed $itemKey): string
     */
    public function reorder(array $items, callable $keyResolver, string|int $sourceKey, int $position): array
    {
        if (empty($items)) {
            return $items;
        }

        $indexByKey = [];
        foreach ($items as $itemKey => $item) {
            $indexByKey[(string) $keyResolver($item, $itemKey)] = $itemKey;
        }

        $sourceKey = (string) $sourceKey;
        if (!array_key_exists($sourceKey, $indexByKey)) {
            return $items;
        }

        $orderedKeys = collect($items)
            ->map(fn($item, $itemKey) => ['key' => $itemKey, 'sorting' => (int) Arr::get($item, 'sorting', 0)])
            ->sortBy('sorting')
            ->pluck('key')
            ->values()
            ->all();

        $sourceItemKey = $indexByKey[$sourceKey];
        $currentPosition = array_search($sourceItemKey, $orderedKeys, true);
        if ($currentPosition === false) {
            return $items;
        }

        array_splice($orderedKeys, $currentPosition, 1);
        array_splice($orderedKeys, max(0, $position), 0, [$sourceItemKey]);

        foreach ($orderedKeys as $newPosition => $itemKey) {
            $items[$itemKey]['sorting'] = $newPosition + 1;
        }

        return $items;
    }
}
