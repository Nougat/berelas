<?php

use App\Data\GrosshandelItem;
use App\Data\KleinanzeigenAd;
use App\Services\Großhandel;
use App\Services\Kleinanzeigen;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public ?Collection $kleinanzeigenAds = null;
    public ?Collection $grosshandelItems = null;

    public bool $kleinanzeigenAvailable = true;
    public string $kleinanzeigenErrorMessage = '';

    public bool $grosshandelAvailable = true;
    public string $grosshandelErrorMessage = '';

    /**
     * Mount the component and fetch the Kleinanzeigen ads and Großhandel items.
     * @param Kleinanzeigen $kleinanzeigen The Kleinanzeigen service to fetch ads from.
     * @param Großhandel $grosshandel The Großhandel service to fetch items from.
     */
    public function mount(Kleinanzeigen $kleinanzeigen, Großhandel $grosshandel)
    {
        $this->kleinanzeigenAds = collect();
        $this->grosshandelItems = collect();

        $this->fetchKleinanzeigenAds($kleinanzeigen);
        $this->fetchGrosshandelItems($grosshandel);
    }

    /**
     * Fetch the Kleinanzeigen ads and handle any errors that may occur during the fetch.
     * @param Kleinanzeigen $kleinanzeigen The Kleinanzeigen service to fetch ads from.
     */
    public function fetchKleinanzeigenAds(Kleinanzeigen $kleinanzeigen)
    {
        try {
            $this->kleinanzeigenAds = $kleinanzeigen->getAds();
            $this->kleinanzeigenAvailable = true;
            $this->kleinanzeigenErrorMessage = '';
        } catch (\Exception $e) {
            $this->kleinanzeigenAvailable = false;
            $this->kleinanzeigenErrorMessage = 'Error fetching Kleinanzeigen ads: ' . $e->getMessage();
        }
    }

    /**
     * Fetch the Großhandel items.
     * @param Großhandel $grosshandel The Großhandel service to fetch items from.
     */
    public function fetchGrosshandelItems(Großhandel $grosshandel)
    {
        try {
            $this->grosshandelItems = $grosshandel->getItems();
            $this->grosshandelAvailable = true;
            $this->grosshandelErrorMessage = '';
        } catch (\Exception $e) {
            $this->grosshandelAvailable = false;
            $this->grosshandelErrorMessage = 'Error fetching Großhandel items: ' . $e->getMessage();
        }
    }

    /*
    |------------------------------------------------------------------------
    | Filtered Computed Properties
    |------------------------------------------------------------------------
    */

    /**
     * GrosshandelItems that have a retail price but no Kleinanzeigen ID.
     */
    #[Computed]
    public function notListed()
    {
        if (!$this->grosshandelAvailable) {
            return collect();
        }
        return $this->grosshandelItems->filter(fn(GrosshandelItem $item) => $item->kleinanzeigenPrice && !$item->kleinanzeigenId);
    }

    /**
     * GrosshandelItems that have a Kleinanzeigen ID but the ID is not found in the list of KleinanzeigenAds.
     * This indicates that the Kleinanzeigen ad was updated or replaced and the ID in the Großhandel sheet was not updated.
     */
    #[Computed]
    public function invalidKleinanzeigenId()
    {
        if (!$this->grosshandelAvailable || !$this->kleinanzeigenAvailable) {
            return collect();
        }
        $validIds = $this->kleinanzeigenAds->pluck('id')->flip();
        return $this->grosshandelItems->filter(function (GrosshandelItem $item) use ($validIds) {
            return $item->kleinanzeigenId && !isset($validIds[$item->kleinanzeigenId]);
        });
    }

    /**
     * GrosshandelItems that have a Kleinanzeigen price but no model information.
     * This indicates that the item was deleted from the Großhandel sheet but the price was not removed.
     */
    #[Computed]
    public function orphanKleinanzeigenPrice()
    {
        if (!$this->grosshandelAvailable) {
            return collect();
        }
        return $this->grosshandelItems->filter(fn(GrosshandelItem $item) => !$item->model && ($item->kleinanzeigenPrice || $item->kleinanzeigenId));
    }

    /**
     * GrosshandelItems that have a Kleinanzeigen price and a valid Kleinanzeigen ID, but the price does not match the price of the corresponding KleinanzeigenAd.
     * This indicates that the price was changed on Kleinanzeigen but not updated in the Großhandel or vice versa.
     */
    #[Computed]
    public function wrongPrice()
    {
        if (!$this->grosshandelAvailable || !$this->kleinanzeigenAvailable) {
            return collect();
        }
        return $this->grosshandelItems->filter(function (GrosshandelItem $item) {
            // no listing means no price to compare
            if (!$item->kleinanzeigenPrice || !$item->kleinanzeigenId) {
                return false;
            }

            // find the matching ad in kleinanzeigenAds and compare the prices
            $MatchingAd = $this->kleinanzeigenAds->first(fn(KleinanzeigenAd $ad) => $ad->id == $item->kleinanzeigenId);
            return $MatchingAd && $MatchingAd->price != $item->kleinanzeigenPrice;
        });
    }

    /**
     * GrosshandelItems that have a comment.
     * This indicates that the item has some special information that needs to be communicated to the user.
     */
    #[Computed]
    public function withComment()
    {
        if (!$this->grosshandelAvailable) {
            return collect();
        }
        return $this->grosshandelItems->filter(fn(GrosshandelItem $item) => $item->comment);
    }

    /**
     * KleinanzeigenAds that have an ID that is not found in the list of GrosshandelItems.
     * This indicates that the GrosshandelItem was deleted from the Großhandel sheet but the ad is still listed on Kleinanzeigen
     * or that the item is not present in the Großhandel sheet at all or that the item is present but the Kleinanzeigen ID was not added to the Großhandel sheet.
     */
    #[Computed]
    public function kleinanzeigenIdNotListed()
    {
        if (!$this->grosshandelAvailable || !$this->kleinanzeigenAvailable) {
            return collect();
        }
        $listedKleinanzeigenIds = $this->grosshandelItems->pluck('kleinanzeigenId');
        return $this->kleinanzeigenAds
            ->filter(function (KleinanzeigenAd $ad) use ($listedKleinanzeigenIds) {
                return !$listedKleinanzeigenIds->contains($ad->id);
            })
            ->values();
    }

    /**
     * Get the KleinanzeigenAd that corresponds to a given GrosshandelItem.
     */
    protected function getKleinanzeigenAd(GrosshandelItem $item): ?KleinanzeigenAd
    {
        return collect($this->kleinanzeigenAds)->firstWhere('id', $item->kleinanzeigenId);
    }

    /**
     * Find possible wholesale matches for a given KleinanzeigenAd.
     */
    protected function findPossibleWholesaleMatches(KleinanzeigenAd $ad): Collection
    {
        return $this->grosshandelItems->filter(fn(GrosshandelItem $item) => $item->similarityScore($ad) === 100)->take(3);
    }
};
?>

<div class="p-4">

    @if ($kleinanzeigenErrorMessage)
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ $kleinanzeigenErrorMessage }}
        </div>
    @endif

    @if ($grosshandelErrorMessage)
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
            {{ $grosshandelErrorMessage }}
        </div>
    @endif

    {{-- Orphaned Kleinanzeigen Prices --}}
    <div class="mt-6 overflow-hidden rounded-xl border-2 border-amber-500 bg-gray-200 shadow-lg">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 border-b-2 border-amber-500 bg-amber-600 px-4 py-3">
            <h2 class="text-xl font-bold text-white">
                Items mit verwaistem Kleinanzeigen-Preis
            </h2>

            <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-bold text-amber-700 shadow">
                {{ $this->grosshandelAvailable ? count($this->orphanKleinanzeigenPrice) : "❌" }}
            </span>
        </div>

        {{-- Items --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($this->orphanKleinanzeigenPrice as $item)
                <div
                    class="group px-4 py-3
                       odd:bg-gray-100 even:bg-gray-200
                       transition-all duration-200
                       hover:bg-amber-100 hover:pl-6">
                    <div class="flex items-center gap-4">

                        {{-- Shelf --}}
                        <span
                            class="shrink-0 min-w-16 rounded-md
                               bg-indigo-600 px-2 py-1 text-center
                               text-xs font-bold text-white shadow-sm">
                            {{ $item->shelf }}
                        </span>

                        {{-- Name + specs --}}
                        <div class="min-w-0 flex-1">

                            <div class="truncate font-bold text-gray-900">
                                {{ $item->displayName() }}
                            </div>

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
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Not Listed Items --}}
    <div class="mt-6 overflow-hidden rounded-xl border-2 border-amber-500 bg-gray-200 shadow-lg">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 border-b-2 border-amber-500 bg-amber-600 px-4 py-3">
            <h2 class="text-xl font-bold text-white">
                Items mit Kleinanzeigen-Preis, aber noch nicht gelistet
            </h2>

            <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-bold text-amber-700 shadow">
                {{ $this->grosshandelAvailable ? count($this->notListed) : "❌" }}
            </span>
        </div>

        {{-- Items --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($this->notListed as $item)
                <div
                    class="group px-4 py-3
                       odd:bg-gray-100 even:bg-gray-200
                       transition-all duration-200
                       hover:bg-amber-100 hover:pl-6">
                    <div class="flex items-center gap-4">

                        {{-- Shelf --}}
                        <span
                            class="shrink-0 min-w-16 rounded-md
             bg-indigo-600 px-2 py-1 text-center
             text-xs font-bold text-white shadow-sm">
                            {{ $item->shelf }}
                        </span>

                        {{-- Name + specs --}}
                        <div class="min-w-0 flex-1">

                            <div class="truncate font-bold text-gray-900">
                                {{ $item->displayName() }}
                            </div>

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
                        </div>

                        {{-- Hover indicator --}}
                        <div
                            class="shrink-0 text-xl font-bold text-gray-400
                               transition-all duration-200
                               group-hover:translate-x-1
                               group-hover:text-amber-600">
                            →
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div> 


    {{-- Invalid Kleinanzeigen IDs --}}
    <div class="mt-6 overflow-hidden rounded-xl border-2 border-amber-500 bg-gray-200 shadow-lg">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 border-b-2 border-amber-500 bg-amber-600 px-4 py-3">
            <h2 class="text-xl font-bold text-white">
                Items mit ungültiger Kleinanzeigen-ID
            </h2>

            <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-bold text-amber-700 shadow">
                {{ $this->grosshandelAvailable && $this->kleinanzeigenAvailable ? count($this->invalidKleinanzeigenId) : "❌" }}
            </span>
        </div>

        {{-- Items --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($this->invalidKleinanzeigenId as $item)
                <div
                    class="group px-4 py-3
                       odd:bg-gray-100 even:bg-gray-200
                       transition-all duration-200
                       hover:bg-amber-100 hover:pl-6">
                    <div class="flex items-center gap-4">

                        {{-- Shelf --}}
                        <span
                            class="shrink-0 min-w-16 rounded-md
                               bg-indigo-600 px-2 py-1 text-center
                               text-xs font-bold text-white shadow-sm">
                            {{ $item->shelf }}
                        </span>

                        {{-- Item + specs --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex items-center gap-2">
                                <span class="truncate font-bold text-gray-900">
                                    {{ $item->displayName() }}
                                </span>

                                {{-- Kleinanzeigen ID --}}
                                <a href="https://www.kleinanzeigen.de/s-anzeige/{{ $item->kleinanzeigenId }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="shrink-0 rounded-md bg-red-600 px-2 py-1
                                       text-xs font-bold text-white shadow-sm
                                       transition hover:bg-red-700"
                                    title="Kleinanzeigen-Anzeige öffnen">
                                    ID {{ $item->kleinanzeigenId }} ↗
                                </a>
                            </div>

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
                        </div>

                        {{-- Richtiger Kleinanzeigen-Preis --}}
                        <div
                            class="shrink-0 rounded-lg bg-emerald-600 px-3 py-2
                               text-right text-white shadow-sm">
                            <div class="text-xs font-medium opacity-80">
                                Richtiger Preis
                            </div>

                            <div class="text-lg font-bold">
                                {{ $item->kleinanzeigenPrice !== null ? number_format($item->kleinanzeigenPrice, 2, ',', '.') . ' €' : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    {{-- Wrong Kleinanzeigen Prices --}}
    <div class="mt-6 overflow-hidden rounded-xl border-2 border-amber-500 bg-gray-200 shadow-lg">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 border-b-2 border-amber-500 bg-amber-600 px-4 py-3">
            <h2 class="text-xl font-bold text-white">
                Items mit falschem Kleinanzeigen-Preis
            </h2>

            <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-bold text-amber-700 shadow">
                {{ $this->grosshandelAvailable && $this->kleinanzeigenAvailable ? count($this->wrongPrice) : "❌" }}
            </span>
        </div>

        {{-- Items --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($this->wrongPrice as $item)
                @php
                    $ad = $this->getKleinanzeigenAd($item);
                @endphp

                <div
                    class="group px-4 py-3
                       odd:bg-gray-100 even:bg-gray-200
                       transition-all duration-200
                       hover:bg-amber-100 hover:pl-6">
                    <div class="flex items-center gap-4">

                        {{-- Shelf --}}
                        <span
                            class="shrink-0 min-w-16 rounded-md
                               bg-indigo-600 px-2 py-1 text-center
                               text-xs font-bold text-white shadow-sm">
                            {{ $item->shelf }}
                        </span>

                        {{-- Item + specs --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex items-center gap-2">

                                <span class="truncate font-bold text-gray-900">
                                    {{ $item->displayName() }}
                                </span>

                                @if ($ad?->url)
                                    <a href="{{ $ad->url }}" target="_blank" rel="noopener noreferrer"
                                        class="shrink-0 rounded-md bg-red-600 px-2 py-1
                                           text-xs font-bold text-white shadow-sm
                                           transition hover:bg-red-700"
                                        title="Kleinanzeigen-Anzeige öffnen">
                                        Anzeige ↗
                                    </a>
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
                        </div>

                        {{-- Prices --}}
                        <div class="flex shrink-0 items-center gap-2">

                            {{-- Tatsächlicher Preis --}}
                            <div class="rounded-lg bg-red-600 px-3 py-2 w-28 h-16 text-right text-white shadow-sm">
                                <div class="text-xs font-medium opacity-80">
                                    Aktuell
                                </div>

                                <div class="text-lg font-bold">
                                    {{ $ad?->formattedPrice() ?? '-' }}
                                </div>
                            </div>

                            {{-- Richtiger Preis --}}
                            <div class="rounded-lg bg-emerald-600 px-3 py-2 w-28 h-16 text-right text-white shadow-sm">
                                <div class="text-xs font-medium opacity-80">
                                    Richtig
                                </div>

                                <div class="text-lg font-bold">
                                    {{ $item->formattedKleinanzeigenPrice() }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    {{-- Items with comments --}}
    <div class="mt-6 overflow-hidden rounded-xl border-2 border-amber-500 bg-gray-200 shadow-lg">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 border-b-2 border-amber-500 bg-amber-600 px-4 py-3">
            <h2 class="text-xl font-bold text-white">
                Items mit Kommentar
            </h2>

            <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-bold text-amber-700 shadow">
                {{ $this->grosshandelAvailable ? count($this->withComment) : "❌" }}
            </span>
        </div>

        {{-- Items --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($this->withComment as $item)
                <div
                    class="group px-4 py-3
                       odd:bg-gray-100 even:bg-gray-200
                       transition-all duration-200
                       hover:bg-amber-100 hover:pl-6">
                    <div class="flex items-center gap-4">

                        {{-- Shelf --}}
                        <span
                            class="shrink-0 min-w-16 rounded-md
                               bg-indigo-600 px-2 py-1 text-center
                               text-xs font-bold text-white shadow-sm">
                            {{ $item->shelf }}
                        </span>

                        {{-- Item --}}
                        <div class="min-w-0 flex-1">

                            {{-- Name --}}
                            <div class="font-bold text-gray-900">
                                {{ $item->displayName() }}
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
                            <div
                                class="mt-2 flex items-start gap-2 rounded-md border-l-4 border-amber-500 bg-white px-3 py-1.5 shadow-sm">
                                <span class="text-sm font-medium leading-tight text-gray-800">
                                    {{ $item->comment }}
                                </span>
                            </div>

                        </div>

                        {{-- Hover indicator --}}
                        <div
                            class="shrink-0 text-xl font-bold text-gray-400
                               transition-all duration-200
                               group-hover:translate-x-1
                               group-hover:text-amber-600">
                            →
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>


    {{-- Kleinanzeigen ads with ID not listed --}}
    <div class="mt-6 overflow-hidden rounded-xl border-2 border-amber-500 bg-gray-200 shadow-lg">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 border-b-2 border-amber-500 bg-amber-600 px-4 py-3">
            <h2 class="text-xl font-bold text-white">
                Kleinanzeigen-Anzeigen ohne Großhandels-Zuordnung
            </h2>

            <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-bold text-amber-700 shadow">
                {{ $this->grosshandelAvailable && $this->kleinanzeigenAvailable ? count($this->kleinanzeigenIdNotListed) : "❌" }}
            </span>
        </div>

        {{-- Ads --}}
        <div class="divide-y-2 divide-gray-300">
            @foreach ($this->kleinanzeigenIdNotListed as $ad)
                <div
                    class="group px-4 py-3
                       odd:bg-gray-100 even:bg-gray-200
                       transition-all duration-200
                       hover:bg-amber-100 hover:pl-6">
                    <div class="flex items-center gap-4">

                        {{-- Anzeige --}}
                        <div class="min-w-0 flex-1">

                            <div class="flex items-center gap-2">

                                <a href="{{ $ad->url }}" target="_blank" rel="noopener noreferrer"
                                    class="truncate font-bold text-gray-900
                                       transition hover:text-amber-700
                                       hover:underline">
                                    {{ $ad->title }}
                                </a>

                                <a href="{{ $ad->url }}" target="_blank" rel="noopener noreferrer"
                                    class="shrink-0 rounded-md bg-red-600 px-2 py-1
                                       text-xs font-bold text-white shadow-sm
                                       transition hover:bg-red-700">
                                    Anzeige ↗
                                </a>

                            </div>

                            {{-- Anzeige-Infos --}}
                            <div class="mt-1 flex flex-wrap gap-1.5 text-xs">

                                @if ($ad->id)
                                    <span class="rounded bg-gray-600 px-2 py-1 font-medium text-white">
                                        ID {{ $ad->id }}
                                    </span>
                                @endif

                                @if ($ad->price !== null)
                                    <span class="rounded bg-emerald-600 px-2 py-1 font-medium text-white">
                                        {{ $ad->formattedPrice() }}
                                    </span>
                                @endif

                                @if ($ad->date)
                                    <span class="rounded bg-gray-500 px-2 py-1 font-medium text-white">
                                        {{ $ad->date }}
                                    </span>
                                @endif

                            </div>

                            {{-- Mögliche Großhandels-Treffer --}}
                            @php
                                $matches = $this->findPossibleWholesaleMatches($ad);
                            @endphp

                            @if ($matches->isNotEmpty())
                                <div class="mt-2 flex flex-wrap items-center gap-2">

                                    <span class="text-xs font-bold text-gray-600">
                                        Möglicher Großhandel:
                                    </span>

                                    @foreach ($matches as $match)
                                        @if ($match->kleinanzeigenId)
                                            <a href="https://www.kleinanzeigen.de/s-anzeige/{{ $match->kleinanzeigenId }}"
                                                target="_blank" rel="noopener noreferrer"
                                                class="group/match inline-flex items-center gap-1 rounded-md
                   border border-blue-400 bg-blue-100 px-2 py-1
                   text-xs font-medium text-blue-900
                   shadow-sm transition
                   hover:border-blue-600 hover:bg-blue-200 hover:text-blue-950">
                                                <span>
                                                    {{ $match->displayName() }}

                                                    @if ($match->cpu)
                                                        · {{ $match->cpu }}
                                                    @endif

                                                    @if ($match->ram)
                                                        · {{ $match->ram }} GB RAM
                                                    @endif

                                                    @if ($match->ssd)
                                                        · {{ $match->ssd }} GB SSD
                                                    @endif

                                                    @if ($match->gpu)
                                                        · {{ $match->gpu }}
                                                    @endif

                                                    @if ($match->layout)
                                                        · {{ $match->layout }}
                                                    @endif

                                                    @if ($match->kleinanzeigenPrice)
                                                        · {{ $match->kleinanzeigenPrice }} €
                                                    @endif

                                                    @if ($match->shelf)
                                                        · Fach {{ $match->shelf }}
                                                    @endif
                                                </span>

                                                <span
                                                    class="shrink-0 font-bold text-blue-500
                       transition group-hover/match:translate-x-0.5
                       group-hover/match:text-blue-700">
                                                    ↗
                                                </span>
                                            </a>
                                        @else
                                            <span
                                                class="rounded-md border border-blue-400
                   bg-blue-100 px-2 py-1
                   text-xs font-medium text-blue-900">
                                                {{ $match->displayName() }}

                                                @if ($match->cpu)
                                                    · {{ $match->cpu }}
                                                @endif

                                                @if ($match->ram)
                                                    · {{ $match->ram }} GB RAM
                                                @endif

                                                @if ($match->ssd)
                                                    · {{ $match->ssd }} GB SSD
                                                @endif

                                                @if ($match->gpu)
                                                    · {{ $match->gpu }}
                                                @endif

                                                @if ($match->layout)
                                                    · {{ $match->layout }}
                                                @endif

                                                @if ($match->kleinanzeigenPrice)
                                                    · {{ $match->kleinanzeigenPrice }} €
                                                @endif

                                                @if ($match->shelf)
                                                    · Fach {{ $match->shelf }}
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach

                                </div>
                            @endif

                        </div>

                        {{-- Arrow --}}
                        <div
                            class="shrink-0 text-xl font-bold text-gray-400
                               transition-all duration-200
                               group-hover:translate-x-1
                               group-hover:text-amber-600">
                            →
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>


</div>
