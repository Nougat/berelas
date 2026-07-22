<?php

namespace App;

class Großhandel
{

    private string $sheetId = "1ztbecW1v7J4EXyuBtGFraQhIoUtzIa8zWe0TdA0g0e8";
    private string $gid = "1469501021";


    public function getItems() : array
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


    public function parseItems(string $json) : array
    {
        $data = json_decode($json, true);

        $table = $data['table'];
        
        $headers = collect($table['cols'])
            ->map(fn ($col) => $col['label'] ?: $col['id'])
            ->toArray();

        $headers[10] = "Layout";
        $headers[11] = "Price";
        $headers[12] = "Kommentar";
        $headers[13] = "KleinanzeigenPrice";
        $headers[14] = "KleinanzeigenId";
        $headers = array_slice($headers, 0, 15);

        return collect($table['rows'])
            ->map(function ($row) use ($headers) {
                $casts = [
                    'Fach' => 'int',
                    'Price' => 'int',
                    'KleinanzeigenPrice' => 'int',
                    'KleinanzeigenId' => 'string',
                    'RAM' => 'int',
                    'SSD' => 'int',
                    'Amount' => 'int',
                ];    
            
                $cells = $row['c'] ?? [];
                $item = [];

                foreach ($headers as $index => $header) {
                    $value = $cells[$index]['v'] ?? null;
                    if ($value !== null && isset($casts[$header])) {
                        $value = match ($casts[$header]) {
                            'int' => (int) $value,
                            'flaot' => (float) $value,
                            'string' => (string) $value,
                            default => $value,
                        };
                    }
                    $item[$header] = $value;
                }
                return $item;
            })
            ->toArray();
    }



}
