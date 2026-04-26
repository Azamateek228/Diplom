<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        // Получаем города с координатами, отсортированные по количеству голосов
        $cities = City::withCount('votes')
            ->orderByDesc('votes_count')
            ->limit(10)
            ->get()
            ->filter(function($city) {
                // Фильтруем только города с координатами
                return $city->lat && $city->lng;
            })
            ->values(); // Переиндексируем массив
        
        $currentCity = Setting::first()?->currentCity;
        
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
