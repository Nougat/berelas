<?php

namespace App\Data;

use App\Concerns\Concerns\WireableArray;
use Livewire\Wireable;

class KleinanzeigenAd implements Wireable
{
    
    use WireableArray;

    /**
     * Create a new class instance.
     */
    private function __construct(
        public readonly ?string $id,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?int $price,
        public readonly ?string $date,
        public readonly ?string $url,
        public readonly ?string $image,
    ) {}

    /**
     * Create object from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: self::string($data['id'] ?? null),
            title: self::string($data['title'] ?? null),
            description: self::string($data['description'] ?? null),
            price: self::int($data['price'] ?? null),
            date: self::string($data['date'] ?? null),
            url: self::string($data['url'] ?? null),
            image: self::string($data['image'] ?? null),
        );    
    }

    /** 
     * Export object to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'date' => $this->date,
            'url' => $this->url,
            'image' => $this->image,
        ];
    }

    /*
    |------------------------------------------------------------------------
    | Helper Functions
    |------------------------------------------------------------------------
    */

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
            $this->id,    
            $this->title,
            $this->price ? $this->formattedPrice() : null,
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
