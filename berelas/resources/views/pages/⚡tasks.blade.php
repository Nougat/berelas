<?php

use App\Großhandel;
use App\Kleinanzeigen;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{

    public array $kleinanzeigenAds = [];
    public array $grosshandelItems = [];

    public function mount(Kleinanzeigen $kleinanzeigen, Großhandel $grosshandel)
    {
        $this->fetch($kleinanzeigen, $grosshandel);
    }

    public function fetch(Kleinanzeigen $kleinanzeigen, Großhandel $grosshandel) 
    {
        $this->kleinanzeigenAds = $kleinanzeigen->getAds();
        $this->grosshandelItems = $grosshandel->getItems();
    }

    #[Computed]
    public function notListed() 
    {
        return array_filter($this->grosshandelItems, fn($item) => ($item['KleinanzeigenPrice'] && !$item['KleinanzeigenId']));
    }

    #[Computed]
    public function invalidKleinanzeigenId()
    {
        $ids = collect($this->kleinanzeigenAds)->pluck('id')->all();
        return array_filter($this->grosshandelItems, fn($item) => ($item['KleinanzeigenId'] && $item['KleinanzeigenId'] != "V" && !in_array($item['KleinanzeigenId'], $ids)));
    }

    #[Computed]
    public function noKleinanzeigenId()
    {
        return array_filter($this->grosshandelItems, fn($item) => ($item['KleinanzeigenId'] == "V"));
    }

    #[Computed]
    public function orphanKleinanzeigenPrice()
    {
        return array_filter($this->grosshandelItems, fn($item) => (!$item['Model']) && $item['KleinanzeigenPrice']);
    }


};
?>

<div class="p-4">
    <span>{{ count($kleinanzeigenAds) }} Kleinanzeigen</span>
    <br>
    <span> {{ count($grosshandelItems) }} Großhandel </span>
    
    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with orphan Kleinanzeigen price: {{ count($this->orphanKleinanzeigenPrice) }} </h2>
        @foreach ($this->orphanKleinanzeigenPrice as $item)
            <div> {{$item['Fach']}} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with Kleinanzeigen price but not listed: {{ count($this->notListed) }} </h2>
        @foreach ($this->notListed as $item)
            <div> {{$item['Fach']}} {{ $item['Model']}} {{ $item['CPU'] }} {{ $item['RAM'] }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items listed at Kleinanzeigen with invalid ID: {{ count($this->invalidKleinanzeigenId) }} </h2>
        @foreach ($this->invalidKleinanzeigenId as $item)
            <div> {{$item['Fach']}} {{ $item['Model']}} {{ $item['CPU'] }} {{ $item['RAM'] }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items listed without Kleinanzeigen ID: {{ count($this->noKleinanzeigenId) }} </h2>
        @foreach ($this->noKleinanzeigenId as $item)
            <div> {{$item['Fach']}} {{ $item['Model']}} {{ $item['CPU'] }} {{ $item['RAM'] }} </div>
        @endforeach
    </div>


</div>