<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filters items based on their shelf location.
 * @implements InventoryItemFilter<InventoryItem>
 */
class LocationFilter implements InventoryItemFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return filled($filterValues['shelfMin'] ?? null) || filled($filterValues['shelfMax'] ?? null);
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<InventoryItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<InventoryItem> Filtered collection of items based on shelf location.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $shelfMin = $filterValues['shelfMin'] ?? null;
        $shelfMax = $filterValues['shelfMax'] ?? null;

        if ($shelfMin === null && $shelfMax === null) {
            return $items; // No filtering needed if both min and max are null
        }

        return $items->filter(function (InventoryItem $item) use ($shelfMin, $shelfMax) {

            $shelf = $item->location;

            if ($shelf === null || $shelf === '') {
                return false; // Exclude items with null or empty shelf
            }

            if ($shelfMin !== null && strnatcasecmp($shelf, $shelfMin) < 0) {
                return false; // Exclude items with shelf less than shelfMin
            }

            if ($shelfMax !== null && strnatcasecmp($shelf, $shelfMax) > 0) {
                return false; // Exclude items with shelf greater than shelfMax
            }

            return true;
        });
    }
}
