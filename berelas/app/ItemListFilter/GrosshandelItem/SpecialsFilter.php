<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for items that have specials. Filters items based on provided specials.
 * @implements ItemListFilter<GrosshandelItem>
 */
class SpecialsFilter implements ItemListFilter
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
     * @param Collection<int, GrosshandelItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, GrosshandelItem> Filtered collection of items based on specials.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $specials = $filterValues['specials'];

        return $items->filter(function (GrosshandelItem $item) use ($specials) {
            return isset($item->specials) && str_contains(strtolower($item->specials), strtolower($specials));
        });
    }
}
