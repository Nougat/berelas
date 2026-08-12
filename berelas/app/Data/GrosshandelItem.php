<?php

namespace App\Data;

use App\Concerns\Concerns\WireableArray;
use Livewire\Wireable;

class GrosshandelItem implements Wireable
{

    use WireableArray;

    /**
     * Create a new class instance.
     */
    private function __construct(
        public readonly ?int $shelf,
        public readonly ?string $manufacturer,
        public readonly ?string $model,
        public readonly ?string $cpu,
        public readonly ?int $ram,
        public readonly ?int $ssd,
        public readonly ?string $gpu,
        public readonly ?int $amount,
        public readonly ?string $condition,
        public readonly ?string $specials,
        public readonly ?string $layout,
        public readonly ?int $price,
        public readonly ?string $comment,
        public readonly ?int $kleinanzeigenPrice,
        public readonly ?string $kleinanzeigenId,
    ) {}

    /**
     * Create object from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            shelf: self::int($data['shelf'] ?? null),
            manufacturer: self::string($data['manufacturer'] ?? null),
            model: self::string($data['model'] ?? null),
            cpu: self::string($data['cpu'] ?? null),
            ram: self::int($data['ram'] ?? null),
            ssd: self::int($data['ssd'] ?? null),
            gpu: self::string($data['gpu'] ?? null),
            amount: self::int($data['amount'] ?? null),
            condition: self::string($data['condition'] ?? null),
            specials: self::string($data['specials'] ?? null),
            layout: self::string($data['layout'] ?? null),
            price: self::int($data['price'] ?? null),
            comment: self::string($data['comment'] ?? null),
            kleinanzeigenPrice: self::int($data['kleinanzeigenPrice'] ?? null),
            kleinanzeigenId: self::string($data['kleinanzeigenId'] ?? null)
        );    
    }

    /** 
     * Export object to array.
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
     * Get the display name of the item.
     */
    public function displayName(): string
    {
        return implode(' ', array_filter([
            $this->manufacturer,
            $this->model,
        ]));
    }

    /**
     * Get the formatted price of the item.
     */
    public function formattedPrice(): string
    {
        return $this->price === null
            ? "-"
            : number_format($this->price, 2, ',', '.') . " €";
    }

    /**
     * Get the string representation of the item.
     */
    public function __toString(): string
    {
        return implode(' | ', array_filter([
            $this->shelf,    
            $this->displayName(),
            $this->cpu,
            $this->ram,
            $this->ssd,
            $this->gpu,
            $this->condition,
            $this->specials,
            $this->layout,
            $this->price ? $this->formattedPrice() : null,
        ]));
    }

    /**
     * Check if the item has a Kleinanzeigen price.
     */
    public function hasKleinanzeigenPrice(): bool
    {
        return $this->kleinanzeigenPrice !== null;
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