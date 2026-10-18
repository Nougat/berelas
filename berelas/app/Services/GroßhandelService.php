<?php

namespace App\Services;

use App\Data\GrosshandelItem;
use Illuminate\Support\Collection;

class GroßhandelService
{

    private string $sheetId = "1ztbecW1v7J4EXyuBtGFraQhIoUtzIa8zWe0TdA0g0e8";
    private array $gids = [
        "main" => "1469501021",
        "tiny" => "1411540712",
        "smartphones" => "1567882437",
        "shop" => "1847407604"
    ];


    public function getItems(): Collection
    {

        $items = collect();

        foreach ($this->gids as $tabName => $gid) {
            $gvizUrl = "https://docs.google.com/spreadsheets/d/{$this->sheetId}/gviz/tq"
            . "?gid={$gid}"
            // . "&tq=" . urlencode("select A, B, C, D, E, F, G, H, I, J, K, L, M, N, O")
            . "&tqx=out:json";
            $itemsJson = $this->fetchItems($gvizUrl);
            $itemsFromSheet = $this->parseItems($itemsJson, $tabName);
            $items = $items->merge($itemsFromSheet);
        }

        return $items;
    }


    public function fetchItems(string $csvUrl): string
    {
        $response = \Illuminate\Support\Facades\Http::get($csvUrl);
        $text = $response->body();

        if (! preg_match('/google\.visualization\.Query\.setResponse\((.*)\);?\s*$/s', trim($text), $matches)) {
            throw new \RuntimeException('Google-Sheets-Antwort konnte nicht extrahiert werden.');
        }
        return $matches[1];
    }


    public function parseItems(string $json, string $tabName): Collection
    {
        $data = json_decode($json, true);

        $headers = [
            "main" => [
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
            ],
            "tiny" => [
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
                'price',
                'comment',
                'kleinanzeigenPrice',
                'kleinanzeigenId',
            ],
            "smartphones" => [
                'shelf',
                'manufacturer',
                'model',
                'ssd',
                'amount',
                'specials',
                'price',
                'comment',
                'kleinanzeigenPrice',
                'kleinanzeigenId',
            ],
            "shop" => [
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
                'kleinanzeigenPrice',
                'comment',
                'kleinanzeigenId',
            ],
        ];

        return collect($data['table']['rows'])
            ->map(function ($row) use ($headers, $tabName) {
                $row = array_map(function ($cell) {
                    return $cell['v'] ?? null;
                }, $row['c']);
                $row = array_combine($headers[$tabName], array_slice($row, 0, count($headers[$tabName])));
                return GrosshandelItem::fromArray($row);
            });
    }

    /*
    |------------------------------------------------------------------------
    | Helper Functions
    |------------------------------------------------------------------------
    */
}
