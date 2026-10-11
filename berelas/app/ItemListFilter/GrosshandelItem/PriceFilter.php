<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for retail price. Filters items based on the specified minimum and maximum retail price values.
 * @implements ItemListFilter<GrosshandelItem>
 */
class PriceFilter implements ItemListFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return $filterValues['priceMin'] !== null || $filterValues['priceMax'] !== null;
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<GrosshandelItem> $items
     * @param array $filterValues
     * @return Collection<GrosshandelItem> Filtered collection of items based on retail price.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        return $items->filter(function (GrosshandelItem $item) use ($filterValues) {
            
            $priceMin = $filterValues['priceMin'];
            $priceMax = $filterValues['priceMax'];

            if ($item->kleinanzeigenPrice === null) {
                return false;
            }

            if ($priceMin !== null && $item->kleinanzeigenPrice < $priceMin) {
                return false;
            }

            if ($priceMax !== null && $item->kleinanzeigenPrice > $priceMax) {
                return false;
            }

            return true;
        });
    }

}
