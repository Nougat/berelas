<?php

use App\Models\InventoryItem;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public ?string $itemId = null;

    public ?string $shelf = null;
    public ?string $manufacturer = null;
    public ?string $model = null;
    public ?string $cpu = null;
    public ?int $ram = null;
    public ?int $ssd = null;
    public ?string $gpu = null;
    public ?int $amount = null;
    public ?string $condition = null;
    public ?string $specials = null;
    public ?string $layout = null;
    public ?int $price = null;
    public ?string $comment = null;

    public array $ramOptions = [8, 16, 32];
    public array $ssdOptions = [128, 256, 512];

    public bool $externalSubmit = false;

    /**
     * Initialize the component.
     */
    public function mount(?string $itemId = null): void
    {
        $this->itemId = $itemId;

        if ($itemId === null) {
            return;
        }

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

    /**
     * Save the inventory item or update it if it already exists.
     */
    #[On('inventory-item-save-request')]
    public function save(): void
    { 
        $validated = $this->validate([
            'shelf' => "nullable|string|max:255",
            'manufacturer' => "nullable|string|max:255",
            'model' => "nullable|string|max:255",
            'cpu' => "nullable|string|max:255",
            'ram' => "nullable|integer|min:0",
            'ssd' => "nullable|integer|min:0",
            'gpu' => "nullable|string|max:255",
            'amount' => "nullable|integer|min:0",
            'condition' => "nullable|string|max:255",
            'specials' => "nullable|string|max:255",
            'layout' => "nullable|string|max:255",
            'price' => "nullable|integer|min:0",
            'comment' => "nullable|string",
        ]);

        if ($this->itemId) {
            $item = InventoryItem::findOrFail($this->itemId);
            $item->update($validated);
        } else {
            $item = InventoryItem::create($validated);
            $this->itemId = $item->id;
        }

        $this->dispatch('inventory-item-saved', InventoryItemId: $item->id);
    }


    public function render()
    {
        return $this->view();
    }
};
?>

<form wire:submit="save">

    <div class="space-y-5">

        {{-- Regal / Menge --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium"> Regal </label>
                <input type="text" wire:model="shelf" class="w-full rounded-lg p-2 bg-gray-100 border-blue-400">

                @error('shelf')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium"> Menge </label>
                <input type="number" wire:model="amount" min="0" class="w-full rounded-lg p-2 bg-gray-100">

                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Hersteller / Modell --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium"> Hersteller </label>
                <input type="text" wire:model="manufacturer" class="w-full rounded-lg p-2 bg-gray-100">

                @error('manufacturer')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium"> Modell </label>
                <input type="text" wire:model="model" class="w-full rounded-lg p-2 bg-gray-100">

                @error('model')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- CPU / GPU --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium">CPU</label>
                <input type="text" wire:model="cpu" class="w-full rounded-lg p-2 bg-gray-100">

                @error('cpu')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">GPU</label>
                <input type="text" wire:model="gpu" class="w-full rounded-lg p-2 bg-gray-100">

                @error('gpu')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- RAM / SSD --}}
        <div class="grid grid-cols-2 gap-4">
            <div x-data="{ customRam: {{ $ram !== null && !in_array($ram, $ramOptions) ? 'true' : 'false' }} }">
                <label class="mb-1 block text-sm font-medium">RAM (GB)</label>
                <div class="flex w-full gap-2 flex-wrap">
                    @foreach($ramOptions as $ramOption)
                        <button type="button" wire:click="$set('ram', {{ $ramOption }})" @click="customRam = false" 
                            class="rounded-lg px-4 py-2"
                            :class = "!customRam && $wire.ram == {{ $ramOption }} ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'">
                            {{ $ramOption }}
                        </button>
                    @endforeach

                    <button type="button" @click="customRam = true"
                        class="rounded-lg px-4 py-2" :class="customRam ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'">
                        Anderes
                    </button>

                    <input x-show="customRam" type="number" wire:model="ram" min="0" class="mt-2 w-full rounded-lg p-2 bg-gray-100">
                </div>
                
                @error('ram')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ customSsd: {{ $ssd !== null && !in_array($ssd, $ssdOptions) ? 'true' : 'false' }} }">
                <label class="mb-1 block text-sm font-medium">SSD (GB)</label>
                <div class="flex w-full gap-2 flex-wrap">
                    @foreach($ssdOptions as $ssdOption)
                        <button type="button" wire:click="$set('ssd', {{ $ssdOption }})" @click="customSsd = false"
                            class="rounded-lg px-4 py-2"
                            :class = "!customSsd && $wire.ssd == {{ $ssdOption }} ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'">
                            {{ $ssdOption }}
                        </button>
                    @endforeach

                    <button type="button" @click="customSsd = true"
                        class="rounded-lg px-4 py-2" :class="customSsd ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'">
                        Anderes
                    </button>

                    <input x-show="customSsd" type="number" wire:model="ssd" min="0" class="mt-2 w-full rounded-lg p-2 bg-gray-100">
                </div>

                @error('ssd')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Zustand --}}
        <div>
            <label class="mb-1 block text-sm font-medium">Zustand</label>
            <input type="text" wire:model="condition" class="w-full rounded-lg p-2 bg-gray-100">

            @error('condition')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

        </div>

        {{-- Besonderheiten --}}
        <div>
            <label class="mb-1 block text-sm font-medium">Besonderheiten</label>
            <input type="text" wire:model="specials" class="w-full rounded-lg p-2 bg-gray-100">

            @error('specials')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Layout --}}
        <div>
            <label class="mb-1 block text-sm font-medium">Layout</label>
            <input type="text" wire:model="layout" class="w-full rounded-lg p-2 bg-gray-100"
                placeholder="QWERTZ, QWERTY, AZERTY, ...">

            @error('layout')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Preis --}}
        <div>
            <label class="mb-1 block text-sm font-medium">Preis (€)</label>
            <input type="number" wire:model="price" min="0" class="w-full rounded-lg p-2 bg-gray-100">

            @error('price')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Kommentar --}}
        <div>
            <label class="mb-1 block text-sm font-medium">Kommentar</label>
            <textarea wire:model="comment" rows="4" class="w-full rounded-lg p-2 bg-gray-100"></textarea>

            @error('comment')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    @if(!$externalSubmit)
    {{-- Footer --}}
    <div class="flex justify-end border-t bg-gray-50 px-6 py-4">
        <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
            <span wire:loading.remove>
                {{ $itemId ? 'Änderungen speichern' : 'Gerät speichern' }}
            </span>
            <span wire:loading>
                Speichern...
            </span>
        </button>
    </div>
    @endif

</form>