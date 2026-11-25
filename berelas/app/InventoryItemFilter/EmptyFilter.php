<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for empty items. Remove empty items from the list.
 * @implements InventoryItemFilter<InventoryItem>
 */
class EmptyFilter implements InventoryItemFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues) : bool
    {
        return $filterValues['nonEmpty'] ?? false;
    }

    /**
     * Apply the filter to the given collection of items.
     * @param Collection<int, InventoryItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, InventoryItem>
     */
    public function apply(Collection $items, array $filterValues) : Collection
    {
        return $items
            ->filter(function (InventoryItem $item) {
                return $item->model !== null && $item->model !== '';
            });
    }

}
