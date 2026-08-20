<?php

use App\Livewire\Forms\InventoryItemForm;
use App\Models\AppleItem;
use App\Services\AppleDeviceDatabaseService;
use Livewire\Component;

new class extends Component {
    public InventoryItemForm $inventory;

    public ?array $appleDevice = null;
    public bool $deviceFound = false;
    public ?string $appleDeviceError = null;

    public ?string $appleItemId = null;
    public ?int $releaseYear = null;
    public ?string $tag = null;

    public ?string $type = null;
    public array $types = ['MacBook Pro', 'MacBook Air', 'iPhone', 'iPad', 'iPad Pro', 'Zubehör', 'Sonstiges'];

    public function mount(?string $appleItemId = null): void
    {
        $this->inventory->manufacturer = 'Apple';

        $this->appleItemId = $appleItemId;
        if ($appleItemId === null) {
            return;
        }

        $appleItem = AppleItem::findOrFail($appleItemId);
        $this->inventory->load($appleItem->inventory_item_id);

        $this->releaseYear = $appleItem->release_year;
        $this->tag = $appleItem->tag;
        $this->type = $appleItem->type;
    }

    public function save(): void
    {
        $this->validate([
            'releaseYear' => 'nullable|integer',
            'tag' => 'nullable|string|max:10',
            'type' => 'nullable|string|max:25',
        ]);

        $inventoryItem = $this->inventory->save();

        $appleItem = AppleItem::updateOrCreate(
            [
                'id' => $this->appleItemId,
            ],
            [
                'inventory_item_id' => $inventoryItem->id,
                'release_year' => $this->releaseYear,
                'tag' => $this->tag,
                'type' => $this->type,
            ],
        );
        $this->appleItemId = $appleItem->id;

        $this->dispatch('apple-item-saved', AppleItemId: $appleItem->id);
    }

    public function findAppleDevice(AppleDeviceDatabaseService $appledb): void
    {
        $this->appleDevice = null;
        $this->deviceFound = false;
        $this->appleDeviceError = null;

        if (blank($this->tag)) {
            return;
        }

        $device = $appledb->get($this->tag);

        if ($device === null) {
            $this->appleDeviceError = 'Kein Gerät mit dieser Apple Nummer gefunden.';
            return;
        }

        $this->appleDevice = $device;
        $this->deviceFound = true;

        $this->releaseYear = $device['release_year'];
        $this->inventory->shelf = $this->determineShelf($device);
        $this->inventory->model = $device['name'];
        $this->inventory->cpu = $device['soc'];

        if (in_array($device['type'], $this->types)) {
            $this->type = $device['type'];
        } else {
            $this->type = 'Sonstiges';
        }
    }

    public function determineShelf(array $device): ?string
    {
        return match ($device['type']) {
            'MacBook Pro', 'MacBook Air' => "{$device['type']} {$device['screen_size']}",

            'iPone' => 'iPhone',

            'iPadPro' => 'iPadPro',
            'iPad Air' => 'iPad Air',
            'iPad' => 'iPad',

            default => 'Sonstiges',
        };
    }

    public function render()
    {
        return $this->view();
    }
};
?>

<form wire:submit="save">
    <div class="max-h-[70vh] space-y-5 overflow-y-auto p-6">

        <div>
            <label class="mb-2 block text-sm font-medium">
                Apple Modellnummer
            </label>

            <div class="flex gap-2">
                <div class="w-full">
                    <input type="text" wire:model="tag" wire:keydown.enter.prevent="findAppleDevice"
                        placeholder="z.B. A23337" class="flex-1 rounded-lg bg-gray-100 p-2 w-full">
                    @if ($appleDeviceError)
                        <p class="mt-1 text-sm text-red-600">
                            {{ $appleDeviceError }}
                        </p>
                    @endif
                </div>

                <button type="button" wire:click="findAppleDevice" wire:loading.attr="disabled"
                    class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50">
                    Erkennen
                </button>
            </div>
        </div>

        @if ($deviceFound)
            <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                <div class="font-medium text-green-800">
                    {{ $appleDevice['name'] }}
                </div>
            </div>
        @endif


        {{-- Gerätetyp --}}
        <div>
            <label class="mb-2 block text-sm font-medium">
                Gerätetyp
            </label>

            <div class="flex flex-wrap gap-2">
                @foreach ($types as $type)
                    <button type="button" wire:click="$set('type', '{{ $type }}')"
                        class="rounded-lg border px-4 py-2 text-sm
                            {{ $this->type === $type
                                ? 'border-blue-600 bg-blue-500 text-white'
                                : 'border-gray-300 bg-white hover:bg-gray-50' }}">
                        {{ $type }}
                    </button>
                @endforeach
            </div>
            {{ $this->type }}
        </div>


        <div class="grid grid-cols-2 gap-4">
            {{-- Erscheinungsjahr --}}
            <div>
                <label class="mb-1 block text-sm font-medium"> Erscheinungsjahr </label>
                <input type="number" wire:model="releaseYear" class="w-full rounded-lg bg-gray-100 p-2">
                @error('releaseYear')
                    <p class="mt-1 text-sm text-red-600"> {{ $message }} </p>
                @enderror
            </div>

            {{-- Tag --}}
            <div>
                <label class="mb-1 block text-sm font-medium"> Tag </label>
                <input type="text" wire:model="tag" class="w-full rounded-lg bg-gray-100 p-2">
                @error('tag')
                    <p class="mt-1 text-sm text-red-600"> {{ $message }} </p>
                @enderror
            </div>
        </div>

        <x-forms.inventory-item-fields />

    </div>

    <div class="flex justify-end border-t bg-gray-50 px-6 py-4">
        <button type="submit" wire:loading.attr="disabled"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
            <span wire:loading.remove> {{ $appleItemId ? 'Änderungen speichern' : 'Apple-Daten speichern' }} </span>
            <span wire:loading> Speichern... </span>
        </button>
    </div>
</form>
