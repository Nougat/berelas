<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for items that have specials. Filters items based on provided specials.
 * @implements InventoryItemFilter<InventoryItem>
 */
class SpecialsFilter implements InventoryItemFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return isset($filterValues['specials']) && $filterValues['specials'] !== '';
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, InventoryItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, InventoryItem> Filtered collection of items based on specials.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $specials = $filterValues['specials'];

        return $items->filter(function (InventoryItem $item) use ($specials) {
            return isset($item->specials) && str_contains(strtolower($item->specials), strtolower($specials));
        });
    }
}
