<?php

namespace App\Support;

use App\Models\City;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class NearbyCitySelector
{
    public static function mapCities(int $limit = 10, ?int $ensureCityId = null): Collection
    {
        return self::naberezhnyeChelnyWithNearest($limit, $ensureCityId);
    }

    public static function naberezhnyeChelnyWithNearest(int $nearest = 10, ?int $ensureCityId = null): Collection
    {
        $allCities = City::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get()
            ->reject(function ($city) {
                $name = mb_strtolower(trim((string) $city->name));
                return $name === 'агрыз' || str_contains($name, 'agryz');
            })
            ->values();

        if (self::hasUsableRouteOrder($allCities)) {
            $orderedCities = $allCities
                ->sortBy(fn ($city) => $city->route_order ?? PHP_INT_MAX)
                ->take($nearest + 1)
                ->values();

            return self::appendEnsuredCity($orderedCities, $ensureCityId);
        }

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
            ->values();

        return self::appendEnsuredCity($cities, $ensureCityId);
    }

    public static function orderedRoute(Collection $cities, ?int $currentCityId = null): Collection
    {
        if ($cities->isEmpty()) {
            return collect();
        }

        if (self::hasUsableRouteOrder($cities)) {
            return $cities
                ->sortBy(fn ($city) => $city->route_order ?? PHP_INT_MAX)
                ->values();
        }

        $remaining = $cities->values()->all();
        $ordered = [];
        $startIndex = 0;

        if ($currentCityId) {
            foreach ($remaining as $idx => $city) {
                if ((int) $city->id === (int) $currentCityId) {
                    $startIndex = $idx;
                    break;
                }
            }
        }

        $current = array_splice($remaining, $startIndex, 1)[0];
        $ordered[] = $current;

        while (!empty($remaining)) {
            $nearestIndex = 0;
            $nearestDistance = INF;

            foreach ($remaining as $idx => $candidate) {
                $distance = self::distanceInKm(
                    (float) $current->lat,
                    (float) $current->lng,
                    (float) $candidate->lat,
                    (float) $candidate->lng
                );

                if ($distance < $nearestDistance) {
                    $nearestDistance = $distance;
                    $nearestIndex = $idx;
                }
            }

            $current = array_splice($remaining, $nearestIndex, 1)[0];
            $ordered[] = $current;
        }

        return collect($ordered)->values();
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
            ->when(self::hasUsableRouteOrder($cities), fn ($collection) => $collection->sortBy(fn ($city) => $city->route_order ?? PHP_INT_MAX))
            ->values();
    }

    private static function hasUsableRouteOrder(Collection $cities): bool
    {
        return self::hasRouteOrder()
            && $cities->contains(fn ($city) => $city->route_order !== null);
    }

    private static function hasRouteOrder(): bool
    {
        static $hasRouteOrder = null;

        if ($hasRouteOrder === null) {
            $hasRouteOrder = Schema::hasColumn('cities', 'route_order');
        }

        return $hasRouteOrder;
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
