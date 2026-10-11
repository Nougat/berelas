<?php

use App\Services\Großhandel;
use Illuminate\Support\Collection;
use Livewire\Component;
use App\Data\GrosshandelItem;
use App\ItemListFilter\EmptyFilter;
use App\ItemListFilter\ItemListFilter;
use App\ItemListFilter\RamFilter;
use App\ItemListFilter\SearchQueryParser;
use Livewire\Attributes\Computed;

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

    public function mount(Großhandel $gh)
    {
        $this->großhandelItems = $gh->getItems()->values();
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
        $filters = [app(EmptyFilter::class), app(RamFilter::class)];

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
                    Show only Kleinanzeigen
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


        <button type="button" wire:click="selectedItems->count() > 0 ? $emit('openModal', 'pages.großhandel-export-modal', ['selectedItems' => $selectedItems]) : null"
            @class([
                'mb-4 rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700',
                'opacity-50 cursor-not-allowed' => $this->selectedItems->count() === 0,
            ])>
            Export selected items ({{ $this->selectedItems->count() }})
        </button>

        <button type="button" wire:click="deselectAll"
            @class([
                'mb-4 ml-2 rounded-lg bg-gray-600 px-4 py-2 text-white transition hover:bg-gray-700',
                'opacity-50 cursor-not-allowed' => $this->selectedItems->count() === 0,
            ])>
            Deselect all ({{ $this->selectedItems->count() }})
        </button>

        {{-- Items --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($this->filteredItems as $key => $item)
                <div wire:key="item-{{ $key }}" wire:click="toggleSelectItem({{ $key }})"
                    @class([
                        'group px-4 py-3 transition-colors duration-200',
                        'bg-amber-200 hover:bg-amber-300 ' => !empty(
                            $selectedItemsKeys[$key]
                        ),
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
                                        wire:click.stop
                                        target="_blank"
                                        rel="noopener noreferrer"
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
