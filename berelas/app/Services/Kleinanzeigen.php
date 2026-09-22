<?php

namespace App\Services;

use App\Data\KleinanzeigenAd;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Kleinanzeigen
{

    /**
     * Fetches all ads for a given seller from the Kleinanzeigen API, handling pagination automatically.
     * @param string $seller The seller's name. Optional, defaults to "berelas-it".
     * @param int $page The page number to fetch. Optional, defaults to 1.
     * @param int $adsPerPage The number of ads to fetch per page. Optional, defaults to 100.
     * @return Collection A collection of KleinanzeigenAd objects.
     * @throws \Exception if the HTTP request fails or returns a non-200 status code.
     */
    public function getAds(string $seller = "berelas-it", int $page = 1, int $adsPerPage = 100): Collection
    {
        $fetch = $this->fetchAds($seller, $page, $adsPerPage);
        $ads = $this->decode($fetch);
        $numAds = $ads->count();

        if ($numAds == 0) return new Collection();
        if ($numAds < $adsPerPage) return $ads;
        else return $ads->merge($this->getAds($seller, $page + 1, $adsPerPage));
    }


    /**
     * Fetches ads from the Kleinanzeigen API for a given seller, page number, and number of ads per page.
     * @param string $seller The seller's name. Optional, defaults to "berelas-it".
     * @param int $page The page number to fetch. Optional, defaults to 1.
     * @param int $adsPerPage The number of ads to fetch per page. Optional, defaults to 100.
     * @return string The raw response body from the API.
     * @throws \Exception if the HTTP request fails or returns a non-200 status code.
     */
    public function fetchAds(string $seller, int $page, int $adsPerPage): string
    {

        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post('https://www.kleinanzeigen.de/_actions/proPublicWeb.brandProfile.getAds/', [
                'brandName' => $seller,
                'pageSize' => $adsPerPage,
                'pageNum' => $page,
            ]);

        if ($response->failed()) {
            throw new \Exception("Failed to fetch ads from Kleinanzeigen. Status code: " . $response->status());
        }

        if ($response->status() !== 200) {
            throw new \Exception("Unexpected response status: " . $response->status());
        }

        return $response->body();
    }

    /**
     * Decodes the JSON response from the Kleinanzeigen API into a collection of KleinanzeigenAd objects.
     * @param string $responseText The raw JSON response from the API.
     * @return Collection A collection of KleinanzeigenAd objects.
     * @throws \Exception if the JSON decoding fails or the expected structure is not present.
     */
    public function decode(string $responseText): Collection
    {

        if ($responseText === null || $responseText === '') {
            throw new \Exception("Response text is empty or null.");
        }

        // Decode the JSON response into an associative array
        $responseArray = json_decode($responseText, true);

        if (!is_array($responseArray)) {
            throw new \Exception("Failed to decode JSON response.");
        }

        if (!isset($responseArray[0]["ads"])) {
            throw new \Exception("Expected 'ads' key not found in response.");
        }

        // Get the indexes of the ads from the response array
        $adsIndexes = $responseArray[$responseArray[0]["ads"]];

        $ads = new Collection();
        foreach ($adsIndexes as $locationIndex) {

            $adInformation = $responseArray[$locationIndex];
            $ad = [];

            // mandatory information
            $ad["id"] = (string)$responseArray[$adInformation["id"]];
            $ad["title"] = $responseArray[$adInformation["title"]];
            $ad["url"] = "https://www.kleinanzeigen.de" . $responseArray[$adInformation["url"]];

            // optional information, check if present
            $ad["image"] = $responseArray[$adInformation["image"] ?? ''] ?? null;
            $rawPrice = $responseArray[$adInformation["price"] ?? ''] ?? null;
            if ($rawPrice !== null) {
                // Entfernt alle Punkte, Kommata und Leerzeichen aus dem String
                $cleanPrice = str_replace(['.', ',', ' '], '', $rawPrice);
                $ad["price"] = (int)$cleanPrice;
            } else {
                $ad["price"] = null;
            }

            $ads[] = KleinanzeigenAd::fromArray($ad);
        }

        return $ads;
    }

    /*
    |------------------------------------------------------------------------
    | Helper Functions
    |------------------------------------------------------------------------
    */
}
