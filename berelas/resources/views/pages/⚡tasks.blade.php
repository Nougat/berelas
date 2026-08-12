<?php

use App\Data\GrosshandelItem;
use App\Data\KleinanzeigenAd;
use App\Services\Großhandel;
use App\Services\Kleinanzeigen;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{

    public Collection $kleinanzeigenAds;
    public Collection $grosshandelItems;

    public function mount(Kleinanzeigen $kleinanzeigen, Großhandel $grosshandel)
    {
        $this->fetch($kleinanzeigen, $grosshandel);
    }

    public function fetch(Kleinanzeigen $kleinanzeigen, Großhandel $grosshandel) 
    {
        $this->kleinanzeigenAds = $kleinanzeigen->getAds();
        $this->grosshandelItems = $grosshandel->getItems();
    }

    /*
    |------------------------------------------------------------------------
    | Filtered Computed Properties
    |------------------------------------------------------------------------
    */

    /**
     * GrosshandelItems that have a Kleinanzeigen price but are not marked as listed on Kleinanzeigen.
     */
    #[Computed]
    public function notListed() 
    {
        return $this->grosshandelItems
            ->filter(fn(GrosshandelItem $item) => ($item->kleinanzeigenPrice && !$item->kleinanzeigenId));
    }

    /**
     * GrosshandelItems that have a Kleinanzeigen ID but the ID is not found in the list of KleinanzeigenAds.
     * This indicates that the item is listed on Kleinanzeigen with another ID, or that the item is not listed on Kleinanzeigen at all.
     */
    #[Computed]
    public function invalidKleinanzeigenId()
    {
        $validIds = $this->kleinanzeigenAds->pluck('id')->flip();
        return $this->grosshandelItems
            ->filter(function (GrosshandelItem $item) use ($validIds) {
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
        return $this->grosshandelItems
            ->filter(fn(GrosshandelItem $item) => (!$item->model && $item->kleinanzeigenPrice));
    }

    /**
     * GrosshandelItems that have a Kleinanzeigen price and a valid Kleinanzeigen ID, but the price does not match the price of the corresponding KleinanzeigenAd.
     * This indicates that the price was changed on Kleinanzeigen but not updated in the Großhandel or vice versa.
     */
    #[Computed]
    public function wrongPrice()
    {
        return $this->grosshandelItems
        ->filter(function(GrosshandelItem $item) {

            // no listing means no price to compare
            if (!$item->kleinanzeigenPrice || !$item->kleinanzeigenId) return false;

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
        return $this->grosshandelItems
            ->filter(fn(GrosshandelItem $item) => $item->comment);
    }

    /**
     * KleinanzeigenAds that have an ID that is not found in the list of GrosshandelItems.
     * This indicates that the GrosshandelItem was deleted from the Großhandel sheet but the ad is still listed on Kleinanzeigen.
     */
    #[Computed]
    public function kleinanzeigenIdNotListed() 
    {
        $listedKleinanzeigenIds = $this->grosshandelItems->pluck('kleinanzeigenId');
        return $this->kleinanzeigenAds
            ->filter(function (KleinanzeigenAd $ad) use ($listedKleinanzeigenIds) {
                return !$listedKleinanzeigenIds->contains($ad->id);
            })
            ->values()
            ->all();
    }


};
?>

<div class="p-4">
    <span>{{ count($kleinanzeigenAds) }} Kleinanzeigen</span>
    <br>
    <span> {{ count($grosshandelItems) }} Großhandel </span>

    {{-- 
    <pre> {{ var_dump($kleinanzeigenAds[0]) }} </pre>
    <pre> {{ var_dump($grosshandelItems[0]) }} </pre>
    --}}
    
    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with orphan Kleinanzeigen price: {{ count($this->orphanKleinanzeigenPrice) }} </h2>
        @foreach ($this->orphanKleinanzeigenPrice as $item)
            <div> {{ $item }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with Kleinanzeigen price but not listed: {{ count($this->notListed) }} </h2>
        @foreach ($this->notListed as $item)
            <div> {{ $item }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items listed at Kleinanzeigen with invalid ID: {{ count($this->invalidKleinanzeigenId) }} </h2>
        @foreach ($this->invalidKleinanzeigenId as $item)
            <div> {{ $item }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with wrong Kleinanzeigen prices: {{ count($this->wrongPrice) }} </h2>
        @foreach ($this->wrongPrice as $item)
            <div> {{ $item }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with comments: {{ count($this->withComment) }} </h2>
        @foreach ($this->withComment as $item)
            <div> {{ $item }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Kleinanzeigen ads with ID not listed: {{ count($this->kleinanzeigenIdNotListed) }} </h2>
        @foreach ($this->kleinanzeigenIdNotListed as $ad)
            <div> <a href="{{ $ad->url }}" target="_blank"> {{ $ad }} </a> </div>
        @endforeach
    </div>


</div>