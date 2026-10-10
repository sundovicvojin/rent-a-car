<?php

namespace App\Services;

use App\Models\CompanySetting;

/**
 * Doplata za dostavu na proizvoljnu tačku sa mape: vazdušna linija od baze (haversine),
 * zaokruženo naviše na pun kilometar, puta cena po kilometru iz podešavanja firme.
 */
class DeliveryService
{
    /** @return array{km:int, fee:int, within_range:bool} */
    public function forPoint(float $lat, float $lng): array
    {
        $cfg = CompanySetting::current()->delivery();
        $km = (int) ceil($this->distanceKm($cfg['lat'], $cfg['lng'], $lat, $lng));

        return ['km' => $km, 'fee' => $km * $cfg['fee_per_km'], 'within_range' => $km <= $cfg['max_km']];
    }

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }
}
