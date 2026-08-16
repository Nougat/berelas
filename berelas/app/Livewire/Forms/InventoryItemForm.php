<?php

namespace App\Livewire\Forms;

use App\Models\InventoryItem;
use Livewire\Attributes\Validate;
use Livewire\Form;

class InventoryItemForm extends Form
{
    
    public ?string $itemId = null;

    #[Validate('nullable|string|max:255')]
    public ?string $shelf = null;

    #[Validate('nullable|string|max:255')]
    public ?string $manufacturer = null;

    #[Validate('nullable|string|max:255')]
    public ?string $model = null;

    #[Validate('nullable|string|max:255')]
    public ?string $cpu = null;

    #[Validate('nullable|integer')]
    public ?int $ram = null;

    #[Validate('nullable|integer')]
    public ?int $ssd = null;

    #[Validate('nullable|string|max:255')]
    public ?string $gpu = null;

    #[Validate('nullable|integer')]
    public ?int $amount = null;

    #[Validate('nullable|string|max:255')]
    public ?string $condition = null;

    #[Validate('nullable|string|max:255')]
    public ?string $specials = null;

    #[Validate('nullable|string|max:255')]
    public ?string $layout = null;

    #[Validate('nullable|integer')]
    public ?int $price = null;

    #[Validate('nullable|string|max:255')]
    public ?string $comment = null;


    public function load(?string $itemId): void
    {
        $this->itemId = $itemId;
        if (!$itemId) return;

        $item = InventoryItem::findOrFail($itemId);
        $this->shelf = $item->shelf;
        $this->manufacturer = $item->manufacturer;
        $this->model = $item->model;
        $this->cpu = $item->cpu;
        $this->ram = $item->ram;
        $this->ssd = $item->ssd;
        $this->gpu = $item->gpu;
        $this->amount = $item->amount;
        $this->condition = $item->condition;
        $this->specials = $item->specials;
        $this->layout = $item->layout;
        $this->price = $item->price;
        $this->comment = $item->comment;
    }

    public function save(): InventoryItem
    {
        $validated = $this->validate();

        if ($this->itemId) {
            $item = InventoryItem::findOrFail($this->itemId);
            $item->update($validated);
        } else {
            $item = InventoryItem::create($validated);
            $this->itemId = $item->id;
        }

        return $item->fresh();
    }

}
