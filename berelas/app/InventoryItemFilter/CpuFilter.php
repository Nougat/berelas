<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for items based on CPU. Filters items based on provided CPU.
 * @implements InventoryItemFilter<InventoryItem>
 */
class CpuFilter implements InventoryItemFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return isset($filterValues['cpu']) && $filterValues['cpu'] !== '';
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, InventoryItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, InventoryItem> Filtered collection of items based on CPU.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $cpu = strtolower($filterValues['cpu']);

        return $items->filter(function (InventoryItem $item) use ($cpu) {
            return isset($item->cpu) && str_contains(strtolower($item->cpu), $cpu);
        });
    }
}
