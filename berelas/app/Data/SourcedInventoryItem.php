<?php

namespace App\Data;

class SourcedInventoryItem implements InventoryItemLike
{

    /**
     * Create a new SourcedInventoryItem instance.
     * @param string $source The source of the inventory item (e.g., 'wholesale', 'retail', etc.)
     * @param InventoryItem $item The inventory item object containing the details of the item
     */
    public function __construct(
        public readonly string $source,
        public readonly InventoryItem $item,
    ) {}

    
    /**
     * Get the underlying InventoryItem object. This method is part of the InventoryItemLike interface, 
     * allowing for polymorphic behavior when dealing with different types of inventory items.
     * @return InventoryItem The inventory item associated with this SourcedInventoryItem.
     */
    public function getItem(): InventoryItem
    {
        return $this->item;
    }
}
