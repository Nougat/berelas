<?php

use App\Kleinanzeigen;
use Livewire\Component;

new class extends Component
{ 
    public array $ads = [];

    public function getAds(Kleinanzeigen $kleinanzeigen)
    {
        $this->ads = $kleinanzeigen->getAds();
    }

};
?>

<div class="space-y-6 p-4">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <button
            wire:click="getAds"
            wire:loading.attr="disabled"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 font-medium text-white shadow transition hover:bg-blue-700 disabled:opacity-50"
        >
            <svg wire:loading.remove xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>

            <svg wire:loading xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>

            <span wire:loading.remove>Anzeigen laden</span>
            <span wire:loading>Lade...</span>
        </button>

        <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
            {{ count($ads) }} Anzeigen gefunden
        </div>
    </div>

    {{-- Grid --}}
    @if(count($ads))
        <div class="grid gap-6 sm:grid-cols-4 xl:grid-cols-6">

            @foreach($ads as $ad)

                <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">

                    {{-- Bild --}}
                    <div class="relative aspect-4/3 overflow-hidden bg-slate-100">

                        @if(!empty($ad['image']))
                            <img
                                src="{{ $ad['image'] }}"
                                alt="{{ $ad['title'] }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                loading="lazy"
                            >
                        @else
                            <div class="flex h-full items-center justify-center text-slate-400">
                                Kein Bild
                            </div>
                        @endif

                    </div>

                    {{-- Inhalt --}}
                    <div class="flex flex-1 flex-col space-y-4 p-5">

                        {{-- Titel --}}
                        <h2 class="line-clamp-3 text-lg font-semibold leading-tight text-slate-900">
                            {{ $ad['title'] }}
                        </h2>

                        {{-- Preis --}}
                        @if(!empty($ad['price']))
                            <div class="text-2xl font-bold text-slate-900">
                                {{ $ad['price'] }}
                            </div>
                        @endif

                        {{-- Technische Badges aus dem Titel --}}
                        <div class="flex flex-wrap gap-2 text-xs">

                            @if(str_contains($ad['title'], 'i3'))
                                <span class="rounded-full bg-blue-100 px-2 py-1 font-medium text-blue-700">
                                    Intel i3
                                </span>
                            @endif

                            @if(str_contains($ad['title'], 'i5'))
                                <span class="rounded-full bg-blue-100 px-2 py-1 font-medium text-blue-700">
                                    Intel i5
                                </span>
                            @endif

                            @if(str_contains($ad['title'], 'i7'))
                                <span class="rounded-full bg-blue-100 px-2 py-1 font-medium text-blue-700">
                                    Intel i7
                                </span>
                            @endif

                            @if(str_contains($ad['title'], '8'))
                                <span class="rounded-full bg-purple-100 px-2 py-1 font-medium text-purple-700">
                                    8 GB
                                </span>
                            @endif

                            @if(str_contains($ad['title'], '16'))
                                <span class="rounded-full bg-purple-100 px-2 py-1 font-medium text-purple-700">
                                    16 GB
                                </span>
                            @endif

                            @if(str_contains($ad['title'], '256'))
                                <span class="rounded-full bg-orange-100 px-2 py-1 font-medium text-orange-700">
                                    256 GB SSD
                                </span>
                            @endif

                            @if(str_contains($ad['title'], '512'))
                                <span class="rounded-full bg-orange-100 px-2 py-1 font-medium text-orange-700">
                                    512 GB SSD
                                </span>
                            @endif

                            @if(str_contains($ad['title'], '4K'))
                                <span class="rounded-full bg-green-100 px-2 py-1 font-medium text-green-700">
                                    4K
                                </span>
                            @endif

                            @if(str_contains($ad['title'], 'Touch'))
                                <span class="rounded-full bg-green-100 px-2 py-1 font-medium text-pink-700">
                                    Touch
                                </span>
                            @endif

                        </div>

                        {{-- ID --}}
                        <div class="text-xs text-slate-500">
                            Anzeige #{{ $ad['id'] }}
                        </div>

                        {{-- Button --}}
                        <div class="mt-auto pt-2">
                            <a
                                href="https://www.kleinanzeigen.de{{ $ad['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 font-medium text-white transition hover:bg-orange-600"
                            >
                                Anzeige öffnen

                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14" />
                                </svg>
                            </a>
                        </div>

                    </div>

                </article>

            @endforeach

        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
            Noch keine Anzeigen geladen.
        </div>
    @endif

</div>