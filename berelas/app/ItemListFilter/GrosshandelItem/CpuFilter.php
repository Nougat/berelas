<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for items based on CPU. Filters items based on provided CPU.
 * @implements ItemListFilter<GrosshandelItem>
 */
class CpuFilter implements ItemListFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return isset($filterValues['cpu']) && $filterValues['cpu'] !== '';
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, GrosshandelItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, GrosshandelItem> Filtered collection of items based on CPU.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $cpu = strtolower($filterValues['cpu']);

        return $items->filter(function (GrosshandelItem $item) use ($cpu) {
            return isset($item->cpu) && str_contains(strtolower($item->cpu), $cpu);
        });
    }
}
