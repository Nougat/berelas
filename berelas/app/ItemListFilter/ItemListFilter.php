<?php

namespace App\ItemListFilter;

use Illuminate\Support\Collection;


/**
 * @template TItem
 */
interface ItemListFilter
{
    /**
     * @param array<string, mixed> $filterValues
     */
    public function isActive(array $filterValues) : bool;

    /**
     * @param Collection<int, TItem> $items
     * @param array<string, mixed> $filterValues
     * @return Collection<int, TItem>
     */
    public function apply(Collection $items, array $filterValues) : Collection;
}
