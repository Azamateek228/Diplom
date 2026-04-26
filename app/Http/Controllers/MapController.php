<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Setting;

class MapController extends Controller
{
    public function index()
    {
        $allCities = City::withCount('votes')
            ->get()
            ->filter(function ($city) {
                return $city->lat && $city->lng;
            })
            ->values();

        $baseCity = $allCities->first(function ($city) {
            $name = mb_strtolower(trim((string) $city->name));
            return str_contains($name, 'набережные челны')
                || str_contains($name, 'наб челны')
                || str_contains($name, 'naberezhnye chelny');
        });

        if ($baseCity) {
            $nearestCities = $allCities
                ->reject(fn ($city) => (int) $city->id === (int) $baseCity->id)
                ->sortBy(fn ($city) => $this->distanceInKm(
                    (float) $baseCity->lat,
                    (float) $baseCity->lng,
                    (float) $city->lat,
                    (float) $city->lng
                ))
                ->take(10)
                ->values();

            $cities = collect([$baseCity])
                ->concat($nearestCities)
                ->values();
        } else {
            $cities = $allCities
                ->sortByDesc('votes_count')
                ->take(11)
                ->values();
        }

        $settingsCurrentCity = Setting::first()?->currentCity;
        $currentCity = $cities->firstWhere('id', $settingsCurrentCity?->id)
            ?? $baseCity
            ?? $cities->first();
        
        // Подготавливаем данные городов для JavaScript
        $citiesData = $cities->map(function($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'lat' => (float)$city->lat,
                'lng' => (float)$city->lng,
                'votes_count' => $city->votes_count ?? 0
            ];
        })->values()->toArray(); // Преобразуем в массив
        
        // Если нет городов с координатами, используем дефолтные координаты России
        $defaultCenter = [55.7558, 37.6173]; // Москва
        
        if ($cities->isNotEmpty()) {
            // Центрируем карту на первом городе или текущем городе
            if ($currentCity && $currentCity->lat && $currentCity->lng) {
                $defaultCenter = [(float)$currentCity->lat, (float)$currentCity->lng];
            } else {
                $firstCity = $cities->first();
                $defaultCenter = [(float)$firstCity->lat, (float)$firstCity->lng];
            }
        }

        return view('map', compact('cities', 'citiesData', 'currentCity', 'defaultCenter'));
    }

    private function distanceInKm(float $lat1, float $lng1, float $lat2, float $lng2): float
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
