<?php

use App\Models\AppleItem;
use App\Models\InventoryItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public bool $showForm = false;
    public ?string $editingId = null;

    public array $typeOrder = ['MacBook Pro', 'MacBook Air', 'iPhone', 'iPad', 'iPad Pro', 'Zubehör', 'Sonstiges'];

    /*
    |--------------------------------------------------------------------------
    | GetItems
    |--------------------------------------------------------------------------
    */
    public function getGroupedAppleItems(): Collection
    {
        return AppleItem::with('inventoryItem')
            ->get()
            ->groupBy(fn($item) => $item->type)
            ->map(function ($items) {
                return $items->groupBy(fn($item) => $item->tag ?: $item->id)->map(function ($items) {
                    return $items->sortBy(function ($item) {
                        return [
                            $item->inventoryItem?->cpu ?? '',
                            $item->inventoryItem?->ram ?? PHP_INT_MAX,
                            $item->inventoryItem?->ssd ?? PHP_INT_MAX,
                            $item->inventoryItem?->layout ?? PHP_INT_MAX,
                            $item->inventoryItem?->condition ?? PHP_INT_MAX,   
                        ];
                    });
                });
            })
            ->sortBy(function ($items, $type) {
                $position = array_search($type, $this->typeOrder);

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
        $items = $this->getGroupedAppleItems();
        $filename = 'Preise.csv';

        return response()->streamDownload(
            function () use ($items) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");

                foreach ($items as $shelf => $shelfItems) {
                    fputcsv($handle, [";$shelf"], ';');

                    foreach ($shelfItems as $appleItem) {
                        $inventoryItem = $appleItem->inventoryItem;
                        fputcsv($handle, [$appleItem->tag ?? 'A0', implode(' | ', array_filter([$inventoryItem->model, $inventoryItem->cpu, $inventoryItem->ram ? $inventoryItem->ram . 'GB RAM' : null, $inventoryItem->ssd ? $inventoryItem->ssd . 'GB SSD' : null])), $inventoryItem->price, $inventoryItem->condition], ';');
                    }

                    fputcsv($handle, [], ';');
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ],
        );
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
        $appleItems = $this->getGroupedAppleItems();

        return $this->view([
            'appleItems' => $appleItems,
            'appleItemCount' => $appleItems->flatten(2)->count(),
        ]);
    }
};
?>

<div class="space-y-6 p-6 pt-0">

    {{-- Header --}}
    <div class="sticky top-0 z-40 -mx-6 flex items-center justify-between border-b bg-white/95 px-6 py-4 backdrop-blur">
        <div>
            <h1 class="text-2xl font-bold">
                Apple Geräte
            </h1>

            <p class="text-sm text-gray-500">
                {{ $appleItemCount }} Geräte
            </p>
        </div>

        <div class="flex gap-2">

            {{-- CSV Export --}}
            <button type="button" wire:click="csvExport" wire:loading.attr="disabled"
                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50">
                <span wire:loading.remove>
                    CSV exportieren
                </span>

                <span wire:loading>
                    Exportiere...
                </span>
            </button>

            {{-- Neues Gerät --}}
            <button type="button" wire:click="create"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Gerät hinzufügen
            </button>

        </div>
    </div>


    {{-- ================================================================ --}}
    {{-- Geräte --}}
    {{-- ================================================================ --}}

    @forelse($appleItems as $shelf => $shelfItems)

        {{-- Regal --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

            <div class="bg-gray-700 px-4 py-3 text-center font-medium text-white">
                {{ $shelf }}
            </div>


            {{-- Modellgruppen --}}
            <div class="divide-y divide-gray-200">

                @foreach ($shelfItems as $tag => $items)
                    @php
                        $first = $items->first();
                        $firstInventory = $first->inventoryItem;
                    @endphp

                    {{-- Modellgruppe --}}
                    <div class="px-4 py-5">

                        {{-- Modellkopf --}}
                        <div class="mb-4 flex items-center gap-3">

                            {{-- Tag --}}
                            <span class="rounded-md bg-gray-800 px-2.5 py-1 font-mono text-xs font-medium text-white">
                                {{ $first->tag ?: 'ohne A-Nummer' }}
                            </span>

                            {{-- Modell --}}
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900">
                                    {{ $firstInventory->manufacturer }}
                                    {{ $firstInventory->model }}
                                </div>

                                @if ($first->release_year)
                                    <div class="text-xs text-gray-500">
                                        {{ $first->release_year }}
                                    </div>
                                @endif
                            </div>
                        </div>


                        {{-- Konfigurationen --}}
                        <div class="overflow-hidden rounded-lg border border-gray-200">

                            {{-- Tabellenkopf --}}
                            <div
                                class="grid grid-cols-[0.5fr_1fr_0.8fr_2fr_2fr_0.3fr_0.3fr_0.3fr] gap-3 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-500">

                                <div>CPU</div>
                                <div>Speicher</div>
                                <div>Layout</div>
                                <div>Zustand</div>
                                <div>Besonderheiten</div>
                                <div>Preis</div>
                                <div>Menge</div>
                                <div></div>

                            </div>


                            {{-- Konfigurationen --}}
                            <div class="divide-y divide-gray-100">

                                @foreach ($items as $appleItem)
                                    @php
                                        $inventoryItem = $appleItem->inventoryItem;
                                    @endphp

                                    <div class="grid grid-cols-[0.5fr_1fr_0.8fr_2fr_2fr_0.3fr_0.3fr_0.3fr] items-center gap-3 border-l-4 border-transparent bg-white px-3 py-2.5 transition hover:bg-blue-50"
                                        wire:click="edit('{{ $appleItem->id }}')">

                                        {{-- CPU --}}
                                        <div class="font-medium text-gray-800">
                                            {{ $inventoryItem->cpu ?: '' }}
                                        </div>

                                        {{-- Speicher --}}
                                        <div class="text-gray-700">
                                            @if ($inventoryItem->ram || $inventoryItem->ssd)
                                                {{ $inventoryItem->ram ? $inventoryItem->ram . ' GB' : '—' }}
                                                <span class="text-gray-400">·</span>
                                                {{ $inventoryItem->ssd ? $inventoryItem->ssd . ' GB' : '—' }}
                                            @else
                                                —
                                            @endif
                                        </div>

                                        {{-- Layout --}}
                                        <div class="text-gray-700">
                                            {{ $inventoryItem->layout ?: '' }}
                                        </div>

                                        {{-- Zustand --}}
                                        <div class="whitespace-nowrap">
                                            @if ($inventoryItem->condition)
                                                <span class="rounded-md bg-gray-100 px-2 py-1 text-xs">
                                                    {{ $inventoryItem->condition }}
                                                </span>
                                            @else
                                            @endif
                                        </div>

                                        {{-- Besonderheiten --}}
                                        <div class="truncate text-gray-600" title="{{ $inventoryItem->specials }}">
                                            {{ $inventoryItem->specials ?: '' }}
                                        </div>

                                        {{-- Preis --}}
                                        <div class="font-semibold text-green-600">
                                            {{ $inventoryItem->price !== null ? number_format($inventoryItem->price, 0, ',', '.') . ' €' : '' }}
                                        </div>

                                        {{-- Menge --}}
                                        <div class="text-center">
                                            {{ $inventoryItem->amount ?? '' }}
                                        </div>

                                        {{-- Aktion --}}
                                        <div class="flex justify-end">

                                            <button type="button" wire:click.stop="edit('{{ $appleItem->id }}')"
                                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                                                title="Bearbeiten">
                                                ✏️
                                            </button>
                                            <button type="button" wire:click.stop="delete('{{ $appleItem->id }}')"
                                                wire:confirm="Wirklich löschen?"
                                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                                                title="Löschen">
                                                ❌
                                            </button>


                                        </div>

                                    </div>
                                @endforeach

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    @empty

        <div class="rounded-xl border border-gray-200 bg-white px-4 py-12 text-center text-gray-500">
            Noch keine Apple-Geräte im Inventar.
        </div>

    @endforelse


    {{-- ================================================================ --}}
    {{-- Modal --}}
    {{-- ================================================================ --}}

    @if ($showForm)

        @teleport('body')
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">

                {{-- Overlay --}}
                <div class="absolute inset-0 bg-black/50" wire:click="closeForm"></div>


                {{-- Dialog --}}
                <div class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between border-b px-6 py-4">

                        <div>

                            <h2 class="text-lg font-semibold">
                                {{ $editingId ? '🍏 Gerät bearbeiten' : '🍏 Gerät hinzufügen' }}
                            </h2>

                            <p class="text-sm text-gray-500">
                                {{ $editingId ? 'Daten des Geräts bearbeiten.' : 'Ein neues Gerät zum Inventar hinzufügen.' }}
                            </p>

                        </div>


                        <button type="button" wire:click="closeForm"
                            class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">
                            ✕
                        </button>

                    </div>


                    {{-- Formular --}}
                    <livewire:forms.apple-item-create-edit-form :apple-item-id="$editingId" :key="'inventory-item-form-' . ($editingId ?? 'new')" />

                </div>

            </div>
        @endteleport

    @endif

</div>
