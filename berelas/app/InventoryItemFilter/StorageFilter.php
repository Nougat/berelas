<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for SSD sizes. Filters items based on the specified minimum and maximum SSD values.
 * @implements InventoryItemFilter<InventoryItem>
 */
class StorageFilter implements InventoryItemFilter
{

    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return filled($filterValues['storageMin'] ?? null) || filled($filterValues['storageMax'] ?? null);
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<InventoryItem> $items
     * @param array $filterValues
     * @return Collection<InventoryItem> Filtered collection of items based on storage size.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        return $items->filter(function (InventoryItem $item) use ($filterValues) {
            
            $storageMin = $filterValues['storageMin'];
            $storageMax = $filterValues['storageMax'];

            if ($item->storage === null) {
                return false;
            }

            if ($storageMin !== null && $item->storage < $storageMin) {
                return false;
            }

            if ($storageMax !== null && $item->storage > $storageMax) {
                return false;
            }

            return true;
        });
    }
}
