<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for items that have a retail price. Filters items based on whether they have a retail price or not.
 * @implements ItemListFilter<GrosshandelItem>
 */
class HasRetailPriceFilter implements ItemListFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return $filterValues['hasRetailPrice'] ?? false;
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, GrosshandelItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, GrosshandelItem> Filtered collection of items based on retail price presence.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        return $items->filter(function (GrosshandelItem $item) {
            return $item->kleinanzeigenPrice !== null;
        });
    }

}
