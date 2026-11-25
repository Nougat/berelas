<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for RAM sizes. Filters items based on the specified minimum and maximum RAM values.
 * @implements InventoryItemFilter<InventoryItem>
 */
class RamFilter implements InventoryItemFilter
{

    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return filled($filterValues['ramMin'] ?? null) || filled($filterValues['ramMax'] ?? null);
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<InventoryItem> $items
     * @param array $filterValues
     * @return Collection<InventoryItem> Filtered collection of items based on RAM size.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        return $items->filter(function (InventoryItem $item) use ($filterValues) {
            
            $ramMin = $filterValues['ramMin'];
            $ramMax = $filterValues['ramMax'];

            if ($item->ram === null) {
                return false;
            }

            if ($ramMin !== null && $item->ram < $ramMin) {
                return false;
            }

            if ($ramMax !== null && $item->ram > $ramMax) {
                return false;
            }

            return true;
        });
    }
    
}
