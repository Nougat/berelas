<?php

namespace App\Services;

use App\Data\InventoryItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class GoogleSheetsInventoryItemService
{

    /**
     * Fetch and parse inventory items from Google Sheets based on the provided source configuration.
     * @param string $source The source configuration key (e.g., "wholesale.main").
     * @return array<string, Collection> An associative array where keys are tab names and values are collections of parsed inventory items.
     * @throws \InvalidArgumentException If the source or tab configuration is not found.
    */
    public function getItems(string $source): array
    {
        // load config 
        $config = config("sheets.{$source}");
        if (!$config) throw new \InvalidArgumentException("No configuration found for source: {$source}");

        // Determine the tab name if specified in the source (e.g., "wholesale.main")
        $tabName = null;
        if (str_contains($source, '.')) {
            [, $tabName] = explode('.', $source, 2);
            if (!isset($config['tabs'][$tabName])) throw new \InvalidArgumentException("No configuration found for tab: {$tabName} in source: {$source}");
        }

        // Get the relevant tabs based on the source and tab name
        $tabs = $tabName ? [$tabName => $config['tabs'][$tabName]] : $config['tabs'];

        // Iterate through each tab and fetch items
        $items = [];
        foreach ($tabs as $tabName => $tabData) {
            $json = $this->fetchItems($this->getGvizUrl($config['sheet_id'], $tabData['gid']));
            $items[$tabName] = $this->parseItems($json, $tabData);
        }

        return $items;
    }

    /**
     * Fetch the raw JSON data from Google Sheets using the Google Visualization API.
     * @param string $GvizUrl The URL to fetch the Google Visualization data.
     * @return string The raw JSON response from Google Sheets.
     * @throws \RuntimeException If the response cannot be extracted from the Google Sheets API.
     */
    public function fetchItems(string $GvizUrl): string
    {
        $response = Http::get($GvizUrl)->throw();
        $text = trim($response->body());

        // Use regex to extract the JSON data from the Google Visualization response
        if (!preg_match(
            '/google\.visualization\.Query\.setResponse\((.*)\);?$/s',
            $text,
            $matches
        )) throw new \RuntimeException('Google-Sheets-Antwort konnte nicht extrahiert werden.');
        
        return $matches[1];
    }

    /**
     * Parse the JSON data from Google Sheets into a collection of InventoryItem objects.
     * @param string $json The raw JSON data from Google Sheets.
     * @param array $tabData The configuration data for the specific tab, including headers and rows to skip.
     * @return Collection A collection of InventoryItem objects parsed from the JSON data.
     * @throws \JsonException If the JSON data cannot be decoded.
     */
    public function parseItems(string $json, array $tabData): Collection
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $headers = $tabData['headers'];
        $skipRows = $tabData['skip_rows'] ?? 0;

        return collect($data['table']['rows'] ?? [])
            ->skip($skipRows)
            ->map(function ($row) use ($headers) {
                
                // Extract cell values from the row, ensuring that missing cells are treated as null
                $cells = array_map(
                    fn($cell) => $cell['v'] ?? null,
                    $row['c'] ?? []
                );
                
                // Pad the values array to match the number of headers, filling missing values with null
                $values = array_pad(
                    array_slice($cells, 0, count($headers)),
                    count($headers),
                    null
                );

                // Combine headers and values into an associative array and create an InventoryItem
                return InventoryItem::fromArray(array_combine($headers, $values));
            })
            ->values();
    }


    /*
    |------------------------------------------------------------------------
    | Helper Functions
    |------------------------------------------------------------------------
    */

    /**
     * Get the URL for fetching Google Visualization data.
     * @param string $sheetId The Google Sheet ID.
     * @param string $gid The GID for the specific tab.
     * @return string The constructed URL for fetching data.
     */
    private function getGvizUrl(string $sheetId, string $gid): string
    {
        return "https://docs.google.com/spreadsheets/d/{$sheetId}/gviz/tq"
            . "?gid={$gid}&tqx=out:json";
    }

}
