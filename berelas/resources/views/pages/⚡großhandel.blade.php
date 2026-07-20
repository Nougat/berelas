<?php

use App\Großhandel;
use Livewire\Component;

new class extends Component
{
    public array $items = [];

    public string $errorMessage;
    public bool $isLoading;

    public function getItems(Großhandel $gh) 
    {
        $this->items = $gh->getItems();
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

        {{-- Headline --}}
        <div class="text-center mb-6" wire:show='!items.length'>
            <h1 class="text-3xl font-bold text-gray-900">Großhandel</h1>
            <p class="mt-2 text-gray-600">This page loads the Google Sheet data directly in browser Javascript.</p>
        </div>

        {{-- Load/Reload Button --}}
        <div class="mb-6 space-y-4">
            <button wire:click="getItems" wire:bind:disabled="isLoading" 
                    class="w-full py-3 px-4  bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    wire:text="isLoading ? 'Loading...' : (items.length ? 'Reload sheet data' : 'Load sheet data')">
            </button>
        </div>

        {{-- Search input and some simple filters --}}
        <div class="mb-6">
            
            <input type="text" placeholder="Search..." wire:model="filterValues.search" wire:show="items.length"
                    class="w-full border border-gray-300 rounded-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
            
            <div wire:show="items.length" class="w-full flex justify-between">
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


    </div>



</div>