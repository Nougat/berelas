<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for items based on display name. Filters items based on provided display name.
 * @implements InventoryItemFilter<InventoryItem>
 */
class DisplayNameFilter implements InventoryItemFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return isset($filterValues['search']) && $filterValues['search'] !== '';
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, InventoryItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, InventoryItem> Filtered collection of items based on display name.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $search = strtolower($filterValues['search']);

        return $items->filter(function (InventoryItem $item) use ($search) {
            return null !== $item->displayName() && str_contains(strtolower($item->displayName()), $search);
        });
    }

}
