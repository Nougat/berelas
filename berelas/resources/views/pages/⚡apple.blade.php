<?php

use App\Models\AppleItem;
use App\Models\InventoryItem;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public bool $showForm = false;
    public ?string $editingId = null;

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
        InventoryItem::findOrFail($id)->delete();
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
        return $this->view([
            'items' => AppleItem::query()
                ->with('inventoryItem')
                ->orderBy('releaseYear')
                ->orderBy('generation')
                ->get(),
        ]);
    }
};
?>

<div class="space-y-6 p-6">
    
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold"> Apple Geräte </h1>
            <p class="text-sm text-gray-500">
                {{ $items->count() }} Geräte
            </p>
        </div>

        <button type="button" wire:click="create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            + Gerät hinzufügen
        </button>
    </div>

    {{-- Tabelle --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <table class="w-full text-left text-sm">
            
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="px-4 py-3"> Regal </th>
                    <th class="px-4 py-3"> Gerät </th>
                    <th class="px-4 py-3"> Jahr </th>
                    <th class="px-4 py-3"> Generation </th>
                    <th class="px-4 py-3"> CPU </th>
                    <th class="px-4 py-3"> RAM </th>
                    <th class="px-4 py-3"> SSD </th>
                    <th class="px-4 py-3"> Menge </th>
                    <th class="px-4 py-3"> Preis </th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($items as $appleItem)

                    @php
                        $item = $appleItem->inventoryItem;
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"> {{ $item->shelf ?: '—' }} </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ $item->manufacturer }}
                                {{ $item->model }}
                            </div>
                            @if($item->condition)
                                <div class="text-xs text-gray-500">
                                    {{ $item->condition }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3"> {{ $appleItem->release_year ?? '—'}}
                        <td class="px-4 py-3"> {{ $appleItem->generation ?? '—'}}
                        <td class="px-4 py-3"> {{ $item->cpu ?: '—' }} </td>
                        <td class="px-4 py-3"> {{ $item->ram ? $item->ram . ' GB' : '—' }} </td>
                        <td class="px-4 py-3"> {{ $item->ssd ? $item->ssd . ' GB' : '—' }} </td>
                        <td class="px-4 py-3"> {{ $item->amount ?? '—' }} </td>
                        <td class="px-4 py-3"> {{ $item->price ?? "—"}} </td>
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