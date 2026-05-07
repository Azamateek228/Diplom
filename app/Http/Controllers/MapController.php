<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\NearbyCitySelector;

class MapController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        $cities = NearbyCitySelector::orderedRoute(
            NearbyCitySelector::mapCities(10, $settings?->current_city_id),
            $settings?->current_city_id
        );

        $currentCity = $cities->first(function ($city) {
            $name = mb_strtolower(trim((string) $city->name));
            return str_contains($name, 'набережные челны') || str_contains($name, 'naberezhnye chelny');
        }) ?? $cities->first();
        
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
}
