<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for items based on their layout. Filters items based on the specified layout value.
 * @implements ItemListFilter<GrosshandelItem>
 */
class LayoutFilter implements ItemListFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return isset($filterValues['layout']) && $filterValues['layout'] !== '';
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, GrosshandelItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, GrosshandelItem> Filtered collection of items based on layout.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $layout = $filterValues['layout'];

        return $items->filter(function (GrosshandelItem $item) use ($layout) {
            return isset($item->layout) && str_contains(strtolower($item->layout), strtolower($layout));
        });
    }
}
