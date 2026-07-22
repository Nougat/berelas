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
        $validIds = collect($this->kleinanzeigenAds)->pluck('id')->flip();
        return collect($this->grosshandelItems)
            ->filter(function ($item) use ($validIds) {
                return $item['KleinanzeigenId'] && !isset($validIds[(int)$item['KleinanzeigenId']]);
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function orphanKleinanzeigenPrice()
    {
        return array_filter($this->grosshandelItems, fn($item) => (!$item['Model']) && $item['KleinanzeigenPrice']);
    }

    #[Computed]
    public function wrongPrice()
    {
        return array_filter($this->grosshandelItems, function($item) {
            $ad = array_first(array_filter($this->kleinanzeigenAds, fn($ad) => $ad['id'] == $item['KleinanzeigenId']));
            if (!$ad) return false;
            return $item['KleinanzeigenPrice'] && $item['KleinanzeigenId'] && $ad['price'] != $item['KleinanzeigenPrice'];
        });
    }

    #[Computed]
    public function withComment() 
    {
        return array_filter($this->grosshandelItems, fn($item) => $item['Kommentar']);
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
            <div> {{$item['Fach']}} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with Kleinanzeigen price but not listed: {{ count($this->notListed) }} </h2>
        @foreach ($this->notListed as $item)
            <div> {{$item['Fach']}} {{ $item['Model']}} {{ $item['CPU'] }} {{ $item['RAM'] }} {{ $item['SSD'] }} {{ $item['Grafik'] }} {{ $item['Spezials'] }} {{ $item['KleinanzeigenPrice'] }}€ </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items listed at Kleinanzeigen with invalid ID: {{ count($this->invalidKleinanzeigenId) }} </h2>
        @foreach ($this->invalidKleinanzeigenId as $item)
            <div> hersteller: {{ $item['Hersteller'] }} fach:{{$item['Fach']}} model:{{ $item['Model']}} cpu:{{ $item['CPU'] }} ram:{{ $item['RAM'] }} ssd: {{ $item['SSD'] }} ID: {{ var_dump($item['KleinanzeigenId']) }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with wrong Kleinanzeigen prices: {{ count($this->wrongPrice) }} </h2>
        @foreach ($this->wrongPrice as $item)
            <div> {{$item['Fach']}} {{ $item['Model']}} {{ $item['CPU'] }} {{ $item['RAM'] }} {{ $item['SSD'] }} </div>
        @endforeach
    </div>

    <div class="mt-4 bg-gray-300 rounded p-2">
        <h2 class="font-bold text-2xl mb-2 border-b-2 border-b-amber-400"> Items with comments: {{ count($this->withComment) }} </h2>
        @foreach ($this->withComment as $item)
            <div> {{$item['Fach']}} {{ $item['Model']}} {{ $item['CPU'] }} {{ $item['RAM'] }} {{ $item['SSD'] }}  {{ $item['Kommentar'] }} </div>
        @endforeach
    </div>


</div>