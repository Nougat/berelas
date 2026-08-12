<?php

namespace App\Concerns\Concerns;

/** 
 * Trait for making arrays wireable in Livewire.
 * Requires the implementing class to have a `fromArray` and `toArray` method.
 *  - public static function fromArray(array $data): self
 *  - public function toArray(): array
 */
trait WireableArray
{
    /**
     * Convert the object to an array for Livewire.
     */
    public function toLivewire(): array
    {
        return $this->toArray();
    }

    /**
     * Create an object from an array for Livewire.
     */
    public static function fromLivewire(mixed $value): static
    {
        return static::fromArray($value);
    }
}
