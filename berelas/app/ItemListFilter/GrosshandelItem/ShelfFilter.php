<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filters items based on their shelf location.
 * @implements ItemListFilter<GrosshandelItem>
 */
class ShelfFilter implements ItemListFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return $filterValues['shelfMin'] !== null || $filterValues['shelfMax'] !== null;
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<GrosshandelItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<GrosshandelItem> Filtered collection of items based on shelf location.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $shelfMin = $filterValues['shelfMin'] ?? null;
        $shelfMax = $filterValues['shelfMax'] ?? null;

        if ($shelfMin === null && $shelfMax === null) {
            return $items; // No filtering needed if both min and max are null
        }

        return $items->filter(function (GrosshandelItem $item) use ($shelfMin, $shelfMax) {

            $shelf = $item->shelf;

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
