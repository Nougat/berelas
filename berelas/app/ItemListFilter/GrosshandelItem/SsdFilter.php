<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for SSD sizes. Filters items based on the specified minimum and maximum SSD values.
 * @implements ItemListFilter<GrosshandelItem>
 */
class SsdFilter implements ItemListFilter
{

    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return $filterValues['ssdMin'] !== null || $filterValues['ssdMax'] !== null;
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<GrosshandelItem> $items
     * @param array $filterValues
     * @return Collection<GrosshandelItem> Filtered collection of items based on SSD size.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        return $items->filter(function (GrosshandelItem $item) use ($filterValues) {
            
            $ssdMin = $filterValues['ssdMin'];
            $ssdMax = $filterValues['ssdMax'];

            if ($item->ssd === null) {
                return false;
            }

            if ($ssdMin !== null && $item->ssd < $ssdMin) {
                return false;
            }

            if ($ssdMax !== null && $item->ssd > $ssdMax) {
                return false;
            }

            return true;
        });
    }
}
