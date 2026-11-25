<?php

namespace App\Concerns;

/** 
 * Trait for making arrays wireable in Livewire.
 * Requires the implementing class to have a `fromArray` and `toArray` method.
 *  - public static function fromArray(array $data): self
 *  - public function toArray(): array
 */
trait WireableArray
{
    /**
     * Create an object from an array.
     * @param array<string, mixed> $data
     * @return static the created object
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Export the object to an array.
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;  


    public function toLivewire(): array
    {
        return $this->toArray();
    }

    public static function fromLivewire(mixed $value): static
    {
        return static::fromArray($value);
    }
}
