<?php

use App\Services\Großhandel;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component {
    public Collection $großhandelItems;
    public string $errorMessage = '';
    public bool $isLoading = false;

    public function getItems(Großhandel $gh): Collection
    {
        return $gh->getItems();
    }

    public function getGhItemsCountProperty(): int
    {
        return $this->großhandelItems->count();
    }

    public function mount(Großhandel $gh)
    {
        $this->großhandelItems = $this->getItems($gh);
    }
};
?>

<div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">

    {{-- Container --}}
    <div class="max-w-5xl w-full bg-white shadow-lg rounded-xl p-6">

        <!-- Error -->
        @if ($errorMessage)
            <div class="text-2xl text-red-500">{{ $errorMessage }}</div>
        @endif


        {{-- Search input and some simple filters --}}
        <div class="mb-6">

            <input type="text" placeholder="Search..." wire:model="filterValues.search"
                class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <div class="w-full flex justify-between">
                <label>
                    <input type="checkbox" wire:model="filterValues.comments" />
                    Show only comments
                </label>

                <label>
                    <input type="checkbox" wire:model="filterValues.orphanPrices" />
                    Show only orphan prices
                </label>

                <label>
                    <input type="checkbox" wire:model="filterValues.nonEmpty" />
                    Show only non-empty
                </label>

                <label>
                    <input type="checkbox" wire:model="filterValues.kleinanzeigen">
                    Show only Kleinanzeigen
                </label>

                <label>
                    <input type="checkbox" wire:model="filterValues.notListed">
                    Show only not listed
                </label>
            </div>
        </div>


        {{-- Items --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($großhandelItems as $item)
                <div
                    class="group px-4 py-3
                       odd:bg-gray-100 even:bg-gray-200
                       transition-all duration-200
                       hover:bg-amber-100 hover:pl-6">
                    <div class="flex items-center gap-4">

                        @if ($item->shelf)
                        {{-- Shelf --}}
                        <span
                            class="shrink-0 min-w-16 rounded-md
                               bg-indigo-600 px-2 py-1 text-center
                               text-xs font-bold text-white shadow-sm">
                            {{ $item->shelf }}
                        </span>
                        @endif

                        {{-- Item --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex items-center gap-2">
                                <span class="truncate font-bold text-gray-900">
                                    {{ $item->displayName() }}
                                </span>

                                @if ($item->amount !== null)
                                    <span class="shrink-0 rounded-md bg-gray-600 px-2 py-1
                                       text-xs font-bold text-white shadow-sm
                                       transition hover:bg-gray-700">
                                        x{{ $item->amount }}
                                    </span>
                                @endif

                                @if ($item->kleinanzeigenId)
                                    {{-- Kleinanzeigen ID --}}
                                    <a href="https://www.kleinanzeigen.de/s-anzeige/{{ $item->kleinanzeigenId }}"
                                        target="_blank" rel="noopener noreferrer"
                                        class="shrink-0 rounded-md bg-yellow-600 px-2 py-1
                                       text-xs font-bold text-white shadow-sm
                                       transition hover:bg-yellow-700"
                                        title="Kleinanzeigen-Anzeige öffnen">
                                        ID {{ $item->kleinanzeigenId }} 
                                        @if ($item->kleinanzeigenPrice)
                                            | {{ $item->kleinanzeigenPrice }}€
                                        @endif
                                    </a>
                                @endif

                                @if ($item->kleinanzeigenPrice && !$item->kleinanzeigenId)
                                    <span class="shrink-0 rounded-md bg-yellow-600 px-2 py-1
                                       text-xs font-bold text-white shadow-sm
                                       transition hover:bg-yellow-700">
                                        {{ $item->kleinanzeigenPrice }}€
                                    </span>
                                @endif

                            </div>

                            {{-- Specs --}}
                            <div class="mt-1 flex flex-wrap gap-1.5 text-xs">

                                @if ($item->cpu)
                                    <span class="rounded bg-blue-600 px-2 py-1 font-medium text-white">
                                        {{ $item->cpu }}
                                    </span>
                                @endif

                                @if ($item->ram)
                                    <span class="rounded bg-emerald-600 px-2 py-1 font-medium text-white">
                                        {{ $item->ram }} GB RAM
                                    </span>
                                @endif

                                @if ($item->ssd)
                                    <span class="rounded bg-violet-600 px-2 py-1 font-medium text-white">
                                        {{ $item->ssd }} GB SSD
                                    </span>
                                @endif

                                @if ($item->gpu)
                                    <span class="rounded bg-red-600 px-2 py-1 font-medium text-white">
                                        {{ $item->gpu }}
                                    </span>
                                @endif

                                @if ($item->layout)
                                    <span class="rounded bg-cyan-600 px-2 py-1 font-medium text-white">
                                        {{ $item->layout }}
                                    </span>
                                @endif

                                @if ($item->condition)
                                    <span class="rounded bg-gray-600 px-2 py-1 font-medium text-white">
                                        {{ $item->condition }}
                                    </span>
                                @endif

                            </div>

                            {{-- Comment --}}
                            @if ($item->comment)
                                <div
                                    class="mt-2 flex items-start gap-2 rounded-md border-l-4 border-amber-500 bg-white px-3 py-1.5 shadow-sm">
                                    <span class="text-sm font-medium leading-tight text-gray-800">
                                        {{ $item->comment }}
                                    </span>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{--
        public readonly ?int $shelf,
        public readonly ?string $manufacturer,
        public readonly ?string $model,
        public readonly ?string $cpu,
        public readonly ?int $ram,
        public readonly ?int $ssd,
        public readonly ?string $gpu,
        public readonly ?int $amount,
        public readonly ?string $condition,
        public readonly ?string $specials,
        public readonly ?string $layout,
        public readonly ?int $price,
        public readonly ?string $comment,
        public readonly ?int $kleinanzeigenPrice,
        public readonly ?string $kleinanzeigenId,
        --}}


    </div>



</div>
