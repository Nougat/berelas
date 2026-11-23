<?php

namespace App\Data;

use App\Concerns\Concerns\WireableArray;
use Livewire\Wireable;

class InventoryItem implements Wireable, InventoryItemLike
{

    // Use WireableArray trait for array serialization and deserialization
    use WireableArray;

    /**
     * Create a new class instance. This is the central definition of the InventoryItem model, encapsulating all relevant properties.
     * Private constructor to enforce the use of the fromArray method for instantiation.
     */
    private function __construct(
        public readonly ?string $location,
        public readonly ?string $brand,
        public readonly ?string $model,
        public readonly ?string $cpu,
        public readonly ?int $ram,
        public readonly ?int $vram,
        public readonly ?int $storage,
        public readonly ?string $gpu,
        public readonly ?int $quantity,
        public readonly ?string $condition,
        public readonly ?string $specials,
        public readonly ?string $keyboardLayout,
        public readonly ?string $comment,
        public readonly ?int $wholesalePrice,
        public readonly ?int $retailPrice,
        public readonly ?string $kleinanzeigenId,
    ) {}

    /**
     * Get the underlying InventoryItem object. This method is part of the InventoryItemLike interface, 
     * allowing for polymorphic behavior when dealing with different types of inventory items.
     * @return InventoryItem The inventory item, in this case, the object itself.
     */
    public function getItem(): InventoryItem
    {
        return $this;
    }

    /*
    |------------------------------------------------------------------------
    | Array Serialization
    |------------------------------------------------------------------------
    */

    /**
     * Create object from array.
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            location: self::string($data['location'] ?? null),
            brand: self::string($data['brand'] ?? null),
            model: self::string($data['model'] ?? null),
            cpu: self::string($data['cpu'] ?? null),
            ram: self::int($data['ram'] ?? null),
            vram: self::int($data['vram'] ?? null),
            storage: self::int($data['storage'] ?? null),
            gpu: self::string($data['gpu'] ?? null),
            quantity: self::int($data['quantity'] ?? null),
            condition: self::string($data['condition'] ?? null),
            specials: self::string($data['specials'] ?? null),
            keyboardLayout: self::string($data['keyboardLayout'] ?? null),
            comment: self::string($data['comment'] ?? null),
            wholesalePrice: self::int($data['wholesalePrice'] ?? null),
            retailPrice: self::int($data['retailPrice'] ?? null),
            kleinanzeigenId: self::string($data['kleinanzeigenId'] ?? null)
        );
    }

    /** 
     * Export object to array.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /*
    |------------------------------------------------------------------------
    | Helper Functions
    |------------------------------------------------------------------------
    */

    /**
     * Check if the InventoryItem has any non-empty data fields.
     * @return bool True if the item has any non-empty data fields, false otherwise.
     */
    public function hasItemData(): bool
    {
        return collect([
            $this->model,
            $this->cpu,
            $this->ram,
            $this->vram,
            $this->storage,
            $this->gpu,
            $this->quantity,
            $this->condition,
            $this->specials, 
            $this->keyboardLayout,
        ])->contains(fn($value) => filled($value));
    }

    /**
     * Get the display name of the item.
     */
    public function displayName(): string
    {
        return implode(' ', array_filter([
            $this->brand,
            $this->model,
        ]));
    }

    /**
     * Get the string representation of the InventoryItem.
     * @return string String representation of the InventoryItem.
     */
    public function __toString(): string
    {
        return implode(' | ', array_filter([
            $this->brand,
            $this->model,
            $this->cpu,
            $this->ram ? $this->ram . 'GB RAM' : null,
            $this->storage ? $this->storage . 'GB SSD' : null,
            $this->gpu,
            $this->vram ? $this->vram . 'GB VRAM' : null,
            $this->condition,
            $this->specials,
            $this->keyboardLayout,
        ]));
    }

    /*
    |------------------------------------------------------------------------
    | Type Casting Helper Functions
    |------------------------------------------------------------------------
    */

    protected static function int(mixed $value): ?int
    {
        if ($value === null) return null;
        if (is_string($value) && trim($value) === '') return null;
        return (int) $value;
    }

    protected static function string(mixed $value): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
