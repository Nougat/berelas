<?php

namespace App\Services;

use App\Data\KleinanzeigenAd;
use Illuminate\Support\Collection;

class Kleinanzeigen
{
    
    public function getAds(string $seller = "berelas-it", $page = 1, $adsPerPage = 100): Collection
    {
        $fetch = $this->fetchAds($seller, $page, $adsPerPage);
        $ads = $this->decode($fetch);
        $numAds = $ads->count();

        if ($numAds == 0) return new Collection();
        if ($numAds < $adsPerPage) return $ads;
        else return $ads->merge($this->getAds($seller, $page+1, $adsPerPage));
    }


    public function fetchAds(string $seller, int $page, int $adsPerPage) : string {
        
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
            // 'Referer' => $baseUrl,
            // 'Origin' => 'https://www.kleinanzeigen.de',
        ])->post('https://www.kleinanzeigen.de/_actions/proPublicWeb.brandProfile.getAds/', [
            'brandName' => $seller,
            'pageSize' => $adsPerPage,
            'pageNum' => $page,
        ]);

        return $response;

    }


    public function decode(string $responseText): Collection {

        $ads = new Collection();

        $responseArray = json_decode($responseText, true);
        $adsIndexes = $responseArray[$responseArray[0]["ads"]];

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
