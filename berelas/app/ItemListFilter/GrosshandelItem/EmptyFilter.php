<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for empty items. Remove empty items from the list.
 * @implements ItemListFilter<GrosshandelItem>
 */
class EmptyFilter implements ItemListFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues) : bool
    {
        return $filterValues['nonEmpty'] ?? false;
    }

    /**
     * Apply the filter to the given collection of items.
     * @param Collection<int, GrosshandelItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, GrosshandelItem>
     */
    public function apply(Collection $items, array $filterValues) : Collection
    {
        return $items
            ->filter(function (GrosshandelItem $item) {
                return $item->model !== null && $item->model !== '';
            });
    }

}
