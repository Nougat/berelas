<?php

use App\Services\Großhandel;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{

    public Collection $großhandelItems;
    public string $errorMessage = "";
    public bool $isLoading = false;


    public function getItems(Großhandel $gh) : Collection
    {
        return $gh->getItems();
    }

    public function getGhItemsCountProperty() : int
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($großhandelItems as $item)
                <div class="bg-gray-50 p-4 rounded-lg shadow-md">
                    <h2 class="text-lg font-semibold">{{ $item->displayName() }}</h2>
                    <p class="text-gray-600">{{ $item->cpu }}</p>
                </div>
            @endforeach

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