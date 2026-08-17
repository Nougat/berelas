<?php

use App\Models\AppleItem;
use App\Models\InventoryItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public bool $showForm = false;
    public ?string $editingId = null;

    public array $shelfOrder = [
        'Pro 13"',
        'Pro 15"',
        'Pro 16"',
        'Air 13"',
        'Air 15"',
        'Air 16"',
        'iPhone',
        'iPad',
        'iPad Pro',
        'Zubehör',
        'Sonstiges',
    ];

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */
    public function mount(): void
    {
        $this->getItems();
    }

    /*
    |--------------------------------------------------------------------------
    | GetItems
    |--------------------------------------------------------------------------
    */
    public function getItems(): Collection
    {
        return InventoryItem::with('appleItem')
            ->get()
            ->groupBy('shelf')
            ->sortBy(function ($items, $shelf) {
                $position = array_search($shelf, $this->shelfOrder);
                return $position === false ? PHP_INT_MAX : $position;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CSV Export
    |--------------------------------------------------------------------------
    */
    public function csvExport()
    {
        $items = $this->getItems();
        $filename = "preise.csv";
        
        return response()->streamDownload(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            
            foreach ($items as $shelf => $shelfItems) {

                fputcsv($handle, [
                    ";$shelf"
                ], ';');

                foreach ($shelfItems as $item) {
                    fputcsv($handle, [
                        $item->appleItem->tag,
                        implode(" | ", array_filter([$item->model, $item->cpu, $item->ram ? $item->ram . "GB RAM" : "", $item->ssd ? $item->ssd . "GB SSD" : ""])),
                        $item->condition,
                        $item->price,
                    ], ';');
                }

                fputcsv($handle, [], ';');
            }

            fclose($handle);

        }, $filename, [
             'Content-Type' => 'text/csv; charset=UTF-8', 
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Neues Item
    |--------------------------------------------------------------------------
    */
    public function create(): void
    {
        $this->editingId = null;
        $this->showForm = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Item bearbeiten
    |--------------------------------------------------------------------------
    */
    public function edit(string $id): void
    {
        $this->editingId = $id;
        $this->showForm = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Item löschen
    |--------------------------------------------------------------------------
    */
    public function delete(string $id): void
    {
        AppleItem::findOrFail($id)->delete();
    }
    
    /*
    |--------------------------------------------------------------------------
    | Formular schließen
    |--------------------------------------------------------------------------
    */
    #[On('apple-item-saved')]
    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
    public function render()
    {
        return $this->view();
    }
};
?>

<div class="space-y-6 p-6">
    
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold"> Apple Geräte </h1>
            <p class="text-sm text-gray-500">
                {{ $this->getItems()->count() }} Geräte
            </p>
        </div>

        <div>
            <button type="button" wire:click="csvExport" wire:loading.attr="disabled" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50">
                <span wire:loading.remove>
                    CSV exportieren
                </span>

                <span wire:loading>
                    Exportiere...
                </span>
            </button>

            <button type="button" wire:click="create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Gerät hinzufügen
            </button>
        </div>
    </div>

    {{-- Tabelle --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <table class="w-full text-left text-sm">
            
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="px-4 py-3"> Tag </th>
                    <th class="px-4 py-3"> Regal </th>
                    <th class="px-4 py-3"> Gerät </th>
                    <th class="px-4 py-3"> Jahr </th>
                    <th class="px-4 py-3"> CPU </th>
                    <th class="px-4 py-3"> RAM </th>
                    <th class="px-4 py-3"> SSD </th>
                    <th class="px-4 py-3"> Menge </th>
                    <th class="px-4 py-3"> Preis </th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                
                @forelse($this->getItems() as $shelf => $shelfItems)

                    <tr>
                        <td colspan="15" class="px-4 py-4 text-center bg-gray-700 text-white">
                            {{ $shelf }}
                        </td>
                    </tr>

                    @foreach($shelfItems as $inventoryItem)

                        @php
                            $appleItem = $inventoryItem->appleItem;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"> {{ $appleItem->tag ?: '—' }} </td>
                            <td class="px-4 py-3"> {{ $inventoryItem->shelf ?: '—' }} </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ $inventoryItem->manufacturer }}
                                    {{ $inventoryItem->model }}
                                </div>
                                @if($inventoryItem->condition)
                                    <div class="text-xs text-gray-500">
                                        {{ $inventoryItem->condition }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3"> {{ $appleItem->release_year ?? '—'}}
                            <td class="px-4 py-3"> {{ $inventoryItem->cpu ?: '—' }} </td>
                            <td class="px-4 py-3"> {{ $inventoryItem->ram ? $inventoryItem->ram . ' GB' : '—' }} </td>
                            <td class="px-4 py-3"> {{ $inventoryItem->ssd ? $inventoryItem->ssd . ' GB' : '—' }} </td>
                            <td class="px-4 py-3"> {{ $inventoryItem->amount ?? '—' }} </td>
                            <td class="px-4 py-3"> {{ $inventoryItem->price ?? "—"}} </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="edit('{{ $appleItem->id }}')" class="rounded-lg px-3 py-1.5 text-sm hover:bg-gray-100">
                                        Bearbeiten
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="delete('{{ $appleItem->id }}')" 
                                        wire:confirm="Möchtest du dieses Gerät wirklich löschen?"
                                        class="rounded-lg px-3 py-1.5 text-sm text-red-600 hover:bg-red-50"
                                    >
                                        Löschen
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            Noch keine Apple-Geräte im Inventar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- Modal --}}
    @if($showForm)
        @teleport('body')
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                {{-- Overlay --}}
                <div
                    class="absolute inset-0 bg-black/50"
                    wire:click="closeForm"
                ></div>
                {{-- Dialog --}}
                <div
                    class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl"
                >
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ $editingId
                                    ? '🍏 Gerät bearbeiten'
                                    : '🍏 Gerät hinzufügen'
                                }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                {{ $editingId
                                    ? 'Daten des Geräts bearbeiten.'
                                    : 'Ein neues Gerät zum Inventar hinzufügen.'
                                }}
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="closeForm"
                            class="rounded-lg p-2 text-gray-400 hover:bg-gray-100"
                        >
                            ✕
                        </button>
                    </div>
                    {{-- Formular --}}
                    <livewire:forms.apple-item-create-edit-form
                        :apple-item-id="$editingId"
                        :key="'inventory-item-form-' . ($editingId ?? 'new')"
                    />
                </div>
            </div>
        @endteleport
    @endif
</div>