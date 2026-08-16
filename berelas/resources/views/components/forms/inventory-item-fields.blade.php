@props([
    'prefix' => 'inventory',
])

<div class="space-y-5">

    {{-- Regal / Menge --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="mb-1 block text-sm font-medium"> Regal </label>
            <input type="text" wire:model="{{ $prefix }}.shelf" class="w-full rounded-lg p-2 bg-gray-100 border-blue-400">
            @error($prefix .'.shelf') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium"> Menge </label>
            <input type="number" wire:model="{{ $prefix }}.amount" min="0" class="w-full rounded-lg p-2 bg-gray-100">
            @error($prefix.'.amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Hersteller / Modell --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="mb-1 block text-sm font-medium"> Hersteller </label>
            <input type="text" wire:model="{{ $prefix }}.manufacturer" class="w-full rounded-lg p-2 bg-gray-100">
            @error($prefix.'.manufacturer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium"> Modell </label>
            <input type="text" wire:model="{{ $prefix }}.model" class="w-full rounded-lg p-2 bg-gray-100">
            @error($prefix.'.model') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- CPU / GPU --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="mb-1 block text-sm font-medium">CPU</label>
            <input type="text" wire:model="{{ $prefix }}.cpu" class="w-full rounded-lg p-2 bg-gray-100">
            @error($prefix.'.cpu') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">GPU</label>
            <input type="text" wire:model="{{ $prefix }}.gpu" class="w-full rounded-lg p-2 bg-gray-100">
            @error($prefix.'.gpu') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- RAM / SSD --}}
    <div class="grid grid-cols-2 gap-4">
        
        <div x-data="{ customRam: $wire.{{ $prefix }}.ram !== null && ![8,16,32].includes($wire.{{ $prefix }}.ram)}">
            <label class="mb-1 block text-sm font-medium">RAM (GB)</label>
            <div class="flex w-full gap-2 flex-wrap">
                @foreach([8, 16, 32] as $ramOption)
                    <button type="button" wire:click="$set('{{ $prefix }}.ram', {{ $ramOption }})" @click="customRam = false" 
                        class="rounded-lg px-4 py-2"
                        :class = "!customRam && $wire.{{ $prefix }}.ram == {{ $ramOption }} ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'">
                        {{ $ramOption }}
                    </button>
                @endforeach

                <button type="button" @click="customRam = true"
                    class="rounded-lg px-4 py-2" :class="customRam ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'">
                    Anderes
                </button>

                <input x-show="customRam" type="number" wire:model="{{ $prefix }}.ram" min="0" class="mt-2 w-full rounded-lg p-2 bg-gray-100">
            </div>
                
            @error($prefix.'.ram') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ customSsd: $wire.{{ $prefix }}.ssd !== null && ![128,256,512].includes($wire.{{ $prefix }}.ssd)}">
            <label class="mb-1 block text-sm font-medium">SSD (GB)</label>
            <div class="flex w-full gap-2 flex-wrap">
                @foreach([128,256,512] as $ssdOption)
                    <button type="button" wire:click="$set('{{ $prefix }}.ssd', {{ $ssdOption }})" @click="customSsd = false"
                        class="rounded-lg px-4 py-2"
                        :class = "!customSsd && $wire.{{ $prefix }}.ssd == {{ $ssdOption }} ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'">
                        {{ $ssdOption }}
                    </button>
                @endforeach

                <button type="button" @click="customSsd = true"
                    class="rounded-lg px-4 py-2" :class="customSsd ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'">
                    Anderes
                </button>

                <input x-show="customSsd" type="number" wire:model="{{ $prefix }}.ssd" min="0" class="mt-2 w-full rounded-lg p-2 bg-gray-100">
            </div>

            @error($prefix.'.ssd') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Zustand --}}
    <div>
        <label class="mb-1 block text-sm font-medium">Zustand</label>
        <input type="text" wire:model="{{ $prefix }}.condition" class="w-full rounded-lg p-2 bg-gray-100">
        @error($prefix.'.condition') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Besonderheiten --}}
    <div>
        <label class="mb-1 block text-sm font-medium">Besonderheiten</label>
        <input type="text" wire:model="{{ $prefix }}.specials" class="w-full rounded-lg p-2 bg-gray-100">
        @error($prefix.'.specials') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Layout --}}
    <div>
        <label class="mb-1 block text-sm font-medium">Layout</label>
        <input type="text" wire:model="{{ $prefix }}.layout" class="w-full rounded-lg p-2 bg-gray-100"
            placeholder="QWERTZ, QWERTY, AZERTY, ...">
        @error($prefix.'.layout') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Preis --}}
    <div>
        <label class="mb-1 block text-sm font-medium">Preis (€)</label>
        <input type="number" wire:model="{{ $prefix }}.price" min="0" class="w-full rounded-lg p-2 bg-gray-100">
        @error($prefix.'.price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Kommentar --}}
    <div>
        <label class="mb-1 block text-sm font-medium">Kommentar</label>
        <textarea wire:model="{{ $prefix }}.comment" rows="4" class="w-full rounded-lg p-2 bg-gray-100"></textarea>
        @error($prefix.'.comment') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>