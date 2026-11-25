<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for items based on their layout. Filters items based on the specified layout value.
 * @implements InventoryItemFilter<InventoryItem>
 */
class KeyboardLayoutFilter implements InventoryItemFilter 
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return isset($filterValues['keyboardLayout']) && $filterValues['keyboardLayout'] !== '';
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, InventoryItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, InventoryItem> Filtered collection of items based on layout.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $keyboardLayout = $filterValues['keyboardLayout'];

        return $items->filter(function (InventoryItem $item) use ($keyboardLayout) {
            return isset($item->keyboardLayout) && str_contains(strtolower($item->keyboardLayout), strtolower($keyboardLayout));
        });
    }
}
