<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for items based on display name. Filters items based on provided display name.
 * @implements ItemListFilter<GrosshandelItem>
*/
class DisplayNameFilter implements ItemListFilter
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
     * @param Collection<int, GrosshandelItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, GrosshandelItem> Filtered collection of items based on display name.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $search = strtolower($filterValues['search']);

        return $items->filter(function (GrosshandelItem $item) use ($search) {
            return null !== $item->displayName() && str_contains(strtolower($item->displayName()), $search);
        });
    }

}
