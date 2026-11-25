<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for items that have a Kleinanzeigen price. Filters items based on whether they have a Kleinanzeigen price or not.
 * @implements InventoryItemFilter<InventoryItem>
 */
class HasKleinanzeigenFilter implements InventoryItemFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return $filterValues['hasKleinanzeigen'] ?? false;
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, InventoryItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, InventoryItem> Filtered collection of items based on Kleinanzeigen price presence.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        return $items->filter(function (InventoryItem $item) {
            return $item->kleinanzeigenId !== null;
        });
    }

}
