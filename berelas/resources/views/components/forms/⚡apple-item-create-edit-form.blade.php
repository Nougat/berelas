<?php

use App\Livewire\Forms\InventoryItemForm;
use App\Models\AppleItem;
use Livewire\Component;

new class extends Component
{
    
    public InventoryItemForm $inventory;

    public ?string $appleItemId = null;
    public ?int $releaseYear = null;
    public ?int $generation = null;


    public function mount(?string $appleItemId = null): void
    {
        $this->inventory->manufacturer = "Apple";

        $this->appleItemId = $appleItemId;
        if ($appleItemId === null) return;

        $appleItem = AppleItem::findOrFail($appleItemId);
        $this->inventory->load($appleItem->inventory_item_id);

        $this->releaseYear = $appleItem->release_year;
        $this->generation = $appleItem->generation;

    }


    public function save(): void
    {
        $this->validate([
            'releaseYear' => 'nullable|integer',
            'generation' => 'nullable|integer',
        ]); 

        $inventoryItem = $this->inventory->save();

        $appleItem = AppleItem::updateOrCreate(
            [
                'id' => $this->appleItemId,
            ],
            [
                'inventory_item_id' => $inventoryItem->id,
                'release_year' => $this->releaseYear,
                'generation' => $this->generation,
            ]
        );
        $this->appleItemId = $appleItem->id;

        $this->dispatch(
            'apple-item-saved',
            AppleItemId: $appleItem->id
        );

    }

    public function render()
    {
        return $this->view();
    }

};
?>

<form wire:submit="save">
    <div class="max-h-[70vh] space-y-5 overflow-y-auto p-6">
        
        <div class="grid grid-cols-2 gap-4">
            {{-- Erscheinungsjahr --}}
            <div>
                <label class="mb-1 block text-sm font-medium"> Erscheinungsjahr </label>
                <input type="number" wire:model="releaseYear" class="w-full rounded-lg bg-gray-100 p-2">
                @error('releaseYear')
                    <p class="mt-1 text-sm text-red-600"> {{ $message }} </p>
                @enderror
            </div>

            {{-- Generation --}}
            <div>
                <label class="mb-1 block text-sm font-medium"> Generation </label>
                <input type="number" wire:model="generation" class="w-full rounded-lg bg-gray-100 p-2">
                @error('generation')
                    <p class="mt-1 text-sm text-red-600"> {{ $message }} </p>
                @enderror
            </div>
        </div>

        <x-forms.inventory-item-fields />

    </div>

    <div class="flex justify-end border-t bg-gray-50 px-6 py-4">
        <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
            <span wire:loading.remove> {{ $appleItemId ? 'Änderungen speichern' : 'Apple-Daten speichern' }} </span>
            <span wire:loading> Speichern... </span>
        </button>
    </div>
</form>