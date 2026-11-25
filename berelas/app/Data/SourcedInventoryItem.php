<?php

namespace App\Data;

class SourcedInventoryItem extends InventoryItem
{

    /**
     * Create a new SourcedInventoryItem instance.
     * @param string $source The source of the inventory item (e.g., 'wholesale', 'retail', etc.)
     * @param mixed ...$itemData The data for the inventory item (must be named arguments)
     */
    public function __construct(
        public readonly string $source,        
        mixed ...$itemData,
    ) {
        parent::__construct(...$itemData);
    }

    /**
     * Create a SourcedInventoryItem instance from an existing InventoryItem and a source.
     * @param InventoryItem $item The inventory item to source.
     * @param string $source The source of the inventory item.
     * @return static The created SourcedInventoryItem instance.
     */
    public static function from(InventoryItem $item, string $source): static
    {
        $data = $item->toArray();
        unset($data['source']);
        return new static($source, ...$data);
    }

    /**
     * Create a SourcedInventoryItem instance from an array of data.
     * @param array $data The data for the inventory item.
     * @return static The created SourcedInventoryItem instance.
     */
    public static function fromArray(array $data): static
    {
        return static::from(parent::fromArray($data), self::string($data['source'] ?? ''));
    }
}
