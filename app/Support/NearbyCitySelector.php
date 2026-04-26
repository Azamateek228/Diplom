<?php

namespace App\Support;

use App\Models\City;
use Illuminate\Support\Collection;

class NearbyCitySelector
{
    public static function naberezhnyeChelnyWithNearest(int $nearest = 10, ?int $ensureCityId = null): Collection
    {
        $allCities = City::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get();

        $baseCity = $allCities->first(function ($city) {
            $name = mb_strtolower(trim((string) $city->name));
            return str_contains($name, 'набережные челны')
                || str_contains($name, 'наб челны')
                || str_contains($name, 'naberezhnye chelny');
        });

        if (!$baseCity) {
            $fallback = $allCities->sortBy('name')->values();
            return self::appendEnsuredCity($fallback, $ensureCityId);
        }

        $cities = collect([$baseCity])
            ->concat(
                $allCities
                    ->reject(fn ($city) => (int) $city->id === (int) $baseCity->id)
                    ->sortBy(fn ($city) => self::distanceInKm(
                        (float) $baseCity->lat,
                        (float) $baseCity->lng,
                        (float) $city->lat,
                        (float) $city->lng
                    ))
                    ->take($nearest)
            )
            ->sortBy('name')
            ->values();

        return self::appendEnsuredCity($cities, $ensureCityId);
    }

    private static function appendEnsuredCity(Collection $cities, ?int $ensureCityId): Collection
    {
        if (!$ensureCityId || $cities->contains(fn ($city) => (int) $city->id === $ensureCityId)) {
            return $cities->values();
        }

        $ensuredCity = City::find($ensureCityId);
        if (!$ensuredCity || !$ensuredCity->lat || !$ensuredCity->lng) {
            return $cities->values();
        }

        return $cities
            ->push($ensuredCity)
            ->sortBy('name')
            ->values();
    }

    private static function distanceInKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
