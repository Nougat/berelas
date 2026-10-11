<?php

namespace App\ItemListFilter\GrosshandelItem;

use App\Data\GrosshandelItem;
use App\ItemListFilter\ItemListFilter;
use Illuminate\Support\Collection;

/**
 * Filter for items that have a specific GPU.
 * @implements ItemListFilter<GrosshandelItem>
 */
class GpuFilter implements ItemListFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @param array<string, mixed> $filterValues
     * @return bool true if the filter is active, false otherwise.
     */
    public function isActive(array $filterValues): bool
    {
        return isset($filterValues['gpu']) && !empty($filterValues['gpu']);
    }

    /**
     * Apply the filter to the collection of items.
     * @param Collection<int, GrosshandelItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, GrosshandelItem> Filtered collection of items based on GPU presence.
     */
    public function apply(Collection $items, array $filterValues): Collection
    {
        $gpuFilterValue = strtolower($filterValues['gpu']);

        return $items->filter(function (GrosshandelItem $item) use ($gpuFilterValue) {
            return isset($item->gpu) && str_contains(strtolower($item->gpu), $gpuFilterValue);
        });
    }

}
