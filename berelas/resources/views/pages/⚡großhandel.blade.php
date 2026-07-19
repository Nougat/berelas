<?php

use App\Großhandel;
use Livewire\Component;

new class extends Component
{
    public array $items = [];

    public function getItems(Großhandel $gh) 
    {
        $this->items = $gh->getItems();
    }

};
?>

<div>


    <button wire:click="getItems" class="bg-blue-400 rounded text-white p-2">
        Get Items
    </button>
    <span> {{ count($items) }} items loaded </span>

    @foreach($items as $item)
        <div class="p-4 border rounded mb-2">
            {{ var_dump($item) }}
        </div>
    @endforeach
</div>