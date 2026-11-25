<?php

namespace App\InventoryItemFilter;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;

/**
 * Filter for retail price. Filters items based on the specified minimum and maximum retail price values.
 * @implements InventoryItemFilter<InventoryItem>
 */
class RetailPriceFilter implements InventoryItemFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return filled($filterValues['retailPriceMin'] ?? null) || filled($filterValues['retailPriceMax'] ?? null);
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<InventoryItem> $items
     * @param array $filterValues
     * @return Collection<InventoryItem> Filtered collection of items based on retail price.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        return $items->filter(function (InventoryItem $item) use ($filterValues) {
            
            $retailPriceMin = $filterValues['retailPriceMin'];
            $retailPriceMax = $filterValues['retailPriceMax'];

            if ($item->retailPrice === null) {
                return false;
            }

            if ($retailPriceMin !== null && $item->retailPrice < $retailPriceMin) {
                return false;
            }

            if ($retailPriceMax !== null && $item->retailPrice > $retailPriceMax) {
                return false;
            }

            return true;
        });
    }

}
