<?php

namespace App\Data;

interface InventoryItemLike
{
    public function getItem(): InventoryItem;
}
