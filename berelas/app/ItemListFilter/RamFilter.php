<?php

namespace App\ItemListFilter;

use App\Data\GrosshandelItem;
use Illuminate\Support\Collection;

/**
 * Filter for RAM sizes. Filters items based on the specified minimum and maximum RAM values.
 * @implements ItemListFilter<GrosshandelItem>
 */
class RamFilter implements ItemListFilter
{

    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return $filterValues['ramMin'] !== null || $filterValues['ramMax'] !== null;
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<GrosshandelItem> $items
     * @param array $filterValues
     * @return Collection<GrosshandelItem> Filtered collection of items based on RAM size.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        return $items->filter(function (GrosshandelItem $item) use ($filterValues) {
            
            $ramMin = $filterValues['ramMin'];
            $ramMax = $filterValues['ramMax'];

            if ($item->ram === null) {
                return false;
            }

            if ($ramMin !== null && $item->ram < $ramMin) {
                return false;
            }

            if ($ramMax !== null && $item->ram > $ramMax) {
                return false;
            }

            return true;
        });
    }
    
}
