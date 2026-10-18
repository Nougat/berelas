<?php

use App\Services\GroßhandelService;
use App\Data\GrosshandelItem;
use App\ItemListFilter\GrosshandelItem\CpuFilter;
use App\ItemListFilter\GrosshandelItem\DisplayNameFilter;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\Computed;

use App\ItemListFilter\ItemListFilter;
use App\ItemListFilter\SearchQueryParser;
use App\ItemListFilter\GrosshandelItem\EmptyFilter;
use App\ItemListFilter\GrosshandelItem\GpuFilter;
use App\ItemListFilter\GrosshandelItem\HasGpuFilter;
use App\ItemListFilter\GrosshandelItem\HasKleinanzeigenFilter;
use App\ItemListFilter\GrosshandelItem\HasRetailPriceFilter;
use App\ItemListFilter\GrosshandelItem\LayoutFilter;
use App\ItemListFilter\GrosshandelItem\PriceFilter;
use App\ItemListFilter\GrosshandelItem\RamFilter;
use App\ItemListFilter\GrosshandelItem\ShelfFilter;
use App\ItemListFilter\GrosshandelItem\SpecialsFilter;
use App\ItemListFilter\GrosshandelItem\SsdFilter;
use Livewire\Attributes\On;

new class extends Component {
    /** @var Collection<int, GrosshandelItem> */
    public Collection $großhandelItems;

    public string $errorMessage = '';
    public bool $isLoading = false;

    public string $searchInput = '';

    public array $checkboxFilters = [
        'nonEmpty' => true,
        'hasKleinanzeigen' => false,
        'hasGpu' => false,
        'hasRetailPrice' => false,
    ];

    public array $selectedItemsKeys = [];

    #[Computed]
    public function selectedItems(): Collection
    {
        $selectedKeys = collect($this->selectedItemsKeys)->filter()->keys()->map(fn($key) => (int) $key);

        return $this->großhandelItems->only($selectedKeys);
    }

    public function toggleSelectItem(int $itemKey): void
    {
        if (isset($this->selectedItemsKeys[$itemKey])) {
            unset($this->selectedItemsKeys[$itemKey]);
        } else {
            $this->selectedItemsKeys[$itemKey] = true;
        }
    }

    public function deselectAll(): void
    {
        $this->selectedItemsKeys = [];
    }

    public function mount(GroßhandelService $gh)
    {
        $this->großhandelItems = $gh
            ->getItems()
            ->sort(function (GrosshandelItem $a, GrosshandelItem $b) {
                $shelfA = $a->shelf;
                $shelfB = $b->shelf;

                // Einträge ohne Fach nach hinten sortieren
                if (empty($shelfA) && !empty($shelfB)) {
                    return 1;
                }

                if (!empty($shelfA) && empty($shelfB)) {
                    return -1;
                }

                // Beide ohne Fach oder beide mit Fach
                return strnatcasecmp($shelfA ?? '', $shelfB ?? '');
            })
            ->values();
    }

    #[Computed]
    public function filterValues(): array
    {
        $parsed = app(SearchQueryParser::class)->parse($this->searchInput);
        return array_merge($this->checkboxFilters, $parsed);
    }

    #[Computed]
    public function filteredItems(): Collection
    {
        /** @var list<ItemListFilter<GrosshandelItem>> $filters */
        $filters = [app(EmptyFilter::class), app(RamFilter::class), app(SsdFilter::class), app(PriceFilter::class), app(HasRetailPriceFilter::class), app(HasKleinanzeigenFilter::class), app(HasGpuFilter::class), app(GpuFilter::class), app(LayoutFilter::class), app(SpecialsFilter::class), app(CpuFilter::class), app(DisplayNameFilter::class), app(ShelfFilter::class)];

        $items = $this->großhandelItems;

        foreach ($filters as $filter) {
            if ($filter->isActive($this->filterValues)) {
                $items = $filter->apply($items, $this->filterValues);
            }
        }
        return $items;
    }

    #[Computed]
    public function itemCount(): int
    {
        return $this->filteredItems()->count();
    }

    /*
     * SignGallery
     */

    public bool $showSignGallery = false;

    public function openSignGallery(): void
    {
        if ($this->selectedItems()->isNotEmpty()) {
            $this->showSignGallery = true;
        }
    }

    #[Computed]
    public function signData(): array
    {
        return $this->selectedItems()
            ->map(
                fn(GrosshandelItem $item, int $key) => [
                    'id' => $key,
                    'manufacturer' => $item->manufacturer ?? '',
                    'device' => $item->displayName(),
                    'title' => 'Ausstattung wie folgt',
                    'lines' => collect([$item->cpu, $item->ram ? "{$item->ram} GB Ram" : null, $item->ssd ? "{$item->ssd} GB SSD" : null, $item->gpu, $item->specials])
                        ->filter()
                        ->implode("\n"),
                    'prefix' => 'nur',
                    'price' => $item->kleinanzeigenPrice ? "{$item->kleinanzeigenPrice}€" : '',
                ],
            )
            ->values()
            ->all();
    }

    #[On('sign-gallery-empty')]
    public function closeSignGallery(): void
    {
        $this->showSignGallery = false;
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

        {{--
        <pre>
            {{ var_dump($this->filterValues) }}
        </pre>
        --}}

        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Großhandel</h1>
            <p class="text-gray-700 mb-4">
                Total items: {{ $this->großhandelItems->count() }} |
                Filtered items: {{ $this->itemCount }}
            </p>
        </div>

        {{-- Search input and some simple filters --}}
        <div class="mb-6">

            <input type="text" placeholder="Search..." wire:model.live.debounce.300ms="searchInput"
                class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <div class="w-full flex gap-2 flex-wrap mt-2 ml-2 text-sm text-gray-700">

                <label>
                    <input type="checkbox" wire:model.live="checkboxFilters.nonEmpty" />
                    Show only non-empty
                </label>

                <label>
                    <input type="checkbox" wire:model.live="checkboxFilters.hasKleinanzeigen">
                    Show only items listed on Kleinanzeigen
                </label>

                <label>
                    <input type="checkbox" wire:model.live="checkboxFilters.hasGpu">
                    Show only items with GPU
                </label>

                <label>
                    <input type="checkbox" wire:model.live="checkboxFilters.hasRetailPrice">
                    Show only items with retail price
                </label>

            </div>
        </div>

        {{-- Items --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($this->filteredItems as $key => $item)
            <div wire:key="item-{{ $key }}" wire:click="toggleSelectItem({{ $key }})"
                @class([ 'group px-4 py-3 transition-colors duration-200' , 'bg-amber-200 hover:bg-amber-300 '=> !empty($selectedItemsKeys[$key]),
                'odd:bg-gray-100 even:bg-gray-200 hover:bg-amber-100' => empty(
                $selectedItemsKeys[$key]
                ),
                ])>
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

                            {{-- Display Name --}}
                            <span class="truncate font-bold text-gray-900">
                                {{ $item->displayName() }}
                            </span>

                            {{-- Amount --}}
                            @if ($item->amount !== null)
                            <span
                                class="shrink-0 rounded-md bg-gray-600 px-2 py-1
                                       text-xs font-bold text-white shadow-sm
                                       transition hover:bg-gray-700">
                                x{{ $item->amount }}
                            </span>
                            @endif

                            {{-- Comment --}}
                            @if ($item->comment)
                            <span
                                class="rounded-md border-l-4 border-amber-500 bg-white px-3 py-1 text-xs font-bold text-gray-800 shadow-sm ">
                                {{ $item->comment }}
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

                            @if ($item->specials)
                            <span class="rounded bg-pink-600 px-2 py-1 font-medium text-white">
                                {{ $item->specials }}
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

                            @if ($item->kleinanzeigenId)
                            {{-- Kleinanzeigen ID --}}
                            <a href="https://www.kleinanzeigen.de/s-anzeige/{{ $item->kleinanzeigenId }}"
                                wire:click.stop target="_blank" rel="noopener noreferrer"
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
                            <span
                                class="shrink-0 rounded-md bg-yellow-600 px-2 py-1
                                       text-xs font-bold text-white shadow-sm
                                       transition hover:bg-yellow-700">
                                {{ $item->kleinanzeigenPrice }}€
                            </span>
                            @endif

                        </div>
                    </div>


                </div>
            </div>
            @endforeach
        </div>

        {{-- Floating action menu --}}
        @if ($this->selectedItems->isNotEmpty())
        <div x-data="{ open: false }" x-on:keydown.escape.window="open = false"
            class="fixed bottom-6 right-6 z-50 w-72">
            {{-- Expandable actions --}}
            <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="mb-3 overflow-hidden rounded-xl border
                   border-gray-700 bg-gray-900 p-2
                   text-white shadow-2xl">
                @php
                $actions = [
                ['icon' => '🏷️', 'label' => 'Label erstellen'],
                ['icon' => '🖨️', 'label' => 'A6-Schilder erstellen', 'action' => 'openSignGallery'],
                ['icon' => '📋', 'label' => 'Kleinanzeigen-Liste'],
                ['icon' => '📊', 'label' => 'Excel-Export'],
                ];
                @endphp

                @foreach ($actions as $action)
                <button type="button" wire:key="selection-action-{{ $loop->index }}"
                    @isset($action['action']) wire:click="{{$action['action']}}" @endisset
                    x-on:click="open = false"
                    class="flex w-full items-center gap-3
                           rounded-lg px-3 py-3 text-left
                           transition hover:bg-gray-700
                           focus:outline-none focus:ring-2
                           focus:ring-blue-500">
                    <span class="text-xl">{{ $action['icon'] }}</span>
                    <span class="font-medium">{{ $action['label'] }}</span>
                    <span class="ml-auto text-gray-500">›</span>
                </button>
                @endforeach
            </div>

            {{-- Main toggle button --}}
            <button type="button" x-on:click="open = !open"
                class="flex w-full items-center gap-3
                   rounded-xl bg-blue-600 px-4 py-3
                   text-left text-white shadow-xl
                   transition hover:bg-blue-700
                   focus:outline-none focus:ring-2
                   focus:ring-blue-400 focus:ring-offset-2"
                :aria-expanded="open">
                <span
                    class="flex h-10 w-10 items-center
                         justify-center rounded-lg bg-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform duration-200"
                        :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>

                <span class="flex-1">
                    <span class="block font-semibold">
                        {{ $this->selectedItems->count() }}
                        Artikel ausgewählt
                    </span>
                    <span class="block text-xs text-blue-100">
                        Aktionen {{ 'anzeigen' }}
                    </span>
                </span>
            </button>

            {{-- Deselect all --}}
            <button type="button" wire:click="deselectAll" x-on:click="open = false"
                class="mt-2 w-full rounded-lg border
                   border-gray-300 bg-white px-3 py-2
                   text-sm text-gray-600 shadow-sm
                   transition hover:bg-gray-100">
                Auswahl aufheben
            </button>
        </div>
        @endif

        @if ($showSignGallery)
        <livewire:sign-gallery :signs="$this->signData" />
        @endif

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