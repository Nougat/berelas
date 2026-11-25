<?php

namespace App\InventoryItemFilter;

use Illuminate\Support\Collection;


/**
 * Interface for filtering inventory items based on specific criteria.
 * @template TItem
 */
interface InventoryItemFilter
{
    /**
     * Determine if the filter is active based on the provided filter values.
     * @return bool True if the filter is active, false otherwise.
     * @param array<string, mixed> $filterValues
     */
    public function isActive(array $filterValues) : bool;

    /**
     * Apply the filter to a collection of inventory items.
     * @param Collection<int, TItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, TItem>
     */
    public function apply(Collection $items, array $filterValues) : Collection;
}
