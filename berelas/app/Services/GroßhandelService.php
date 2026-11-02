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
        "accessories" => "1283099629",
    ];


    public function getItems(): Collection
    {

        // Fetch and parse items from main sheet
        $mainItems = $this->parseItems(
            $this->fetchItems($this->getGvizUrl($this->gids['main'])),
            'main'
        );

        
        $tabItems = collect();
        foreach ($this->gids as $tabName => $gid) {
            
            // Skip the main tab as it's already processed
            if ($tabName === 'main') continue;

            // Fetch and parse items from the current tab
            $itemsFromTab = $this->parseItems(
                $this->fetchItems($this->getGvizUrl($gid)),
                $tabName
            );

            // Concatenate the filtered items from the current tab to the main collection
            $tabItems = $tabItems->concat($itemsFromTab);
        }

        // Filter out main items that have no item data and are also present in tab items
        $mainItems = $mainItems->filter(function (GrosshandelItem $item) use ($tabItems) {
            return $item->hasItemData() || $tabItems->doesntContain('shelf', $item->shelf);
        });

        // Merge main items with tab items
        return $mainItems->concat($tabItems);

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
            "accessories" => [
                'shelf',
                'manufacturer',
                'model',
                'amount',
                'specials',
                'price',
                'comment',
                'kleinanzeigenPrice',
                'kleinanzeigenId',
            ]
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

    /**
     * Get the URL for fetching Google Visualization data.
     * @param string $gid The Google Sheet GID for the specific tab.
     * @return string The constructed URL for fetching data.
     */
    private function getGvizUrl(string $gid): string
    {
        return "https://docs.google.com/spreadsheets/d/{$this->sheetId}/gviz/tq"
            . "?gid={$gid}&tqx=out:json";
    }

    /**
     * Normalize the shelf value by trimming whitespace and converting to lowercase.
     * @param string|null $shelf The shelf value to normalize.
     * @return string|null The normalized shelf value or null if empty.
     */
    private function normalizeShelf(?string $shelf): ?string
    {
        $shelf = mb_strtolower(trim($shelf ?? ''));
        return $shelf !== '' ? $shelf : null;
    }
}
