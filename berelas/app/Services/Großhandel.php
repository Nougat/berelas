<?php

namespace App\Services;

use App\Data\GrosshandelItem;
use Illuminate\Support\Collection;

class Großhandel
{

    private string $sheetId = "1ztbecW1v7J4EXyuBtGFraQhIoUtzIa8zWe0TdA0g0e8";
    private string $gid = "1469501021";


    public function getItems() : Collection
    {
        $gvizUrl = "https://docs.google.com/spreadsheets/d/{$this->sheetId}/gviz/tq?gid={$this->gid}&tqx=out:json";
        $itemsJson = $this->fetchItems($gvizUrl);
        $items = $this->parseItems($itemsJson);
        
        return $items;
    }


    public function fetchItems(string $csvUrl) : string
    {
        $response = \Illuminate\Support\Facades\Http::get($csvUrl);
        $text = $response->body();

        preg_match('/setResponse\\((.*)\\);$/s', $text, $matches);
        $json = $matches[1] ?? '{}';

        return $json;
    }


    public function parseItems(string $json): Collection
    {
        $data = json_decode($json, true);
        $headers = [
            'shelf',
            'manufacturer',
            'model',
            'cpu',
            'ram',
            'ssd',
            'gpu',
            'amount',
            'condition',
            'specials',
            'layout',
            'price',
            'comment',
            'kleinanzeigenPrice',
            'kleinanzeigenId',
        ];

        // dd($data['table']['rows'][0]);

        return collect($data['table']['rows'])
            ->map(function ($row) use ($headers) {
                $row = array_map(function ($cell) {
                    return $cell['v'] ?? null;
                }, $row['c']);
                $row = array_combine($headers, array_slice($row, 0, count($headers)));
                return GrosshandelItem::fromArray($row);
            });
    }

    /*
    |------------------------------------------------------------------------
    | Helper Functions
    |------------------------------------------------------------------------
    */

    /**
     * Get the first GrosshandelItem that has a Kleinanzeigen price but no Kleinanzeigen ID.
     */
    public function getNextItemForKleinanzeigen()
    {
        return $this->getItems()
            ->filter(fn(GrosshandelItem $item) => ($item->kleinanzeigenPrice && !$item->kleinanzeigenId))
            ->first();
    }


}