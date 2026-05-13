<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\NearbyCitySelector;

class MapController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        $cities = NearbyCitySelector::actualRoute();
        $routeType = NearbyCitySelector::actualRouteType();
        $routeLabel = NearbyCitySelector::actualRouteLabel();

        $currentCity = $settings?->current_city_id
            ? $cities->first(fn ($city) => (int) $city->id === (int) $settings->current_city_id)
            : null;

        $currentCity ??= $cities->first();

        $citiesData = $cities->map(function ($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'lat' => $city->lat !== null ? (float) $city->lat : null,
                'lng' => $city->lng !== null ? (float) $city->lng : null,
                'route_order' => $city->route_order,
                'votes_count' => $city->votes_count ?? 0,
            ];
        })->values()->toArray();

        $defaultCenter = [55.7558, 37.6173];

        $centerCity = $currentCity && $currentCity->lat && $currentCity->lng
            ? $currentCity
            : $cities->first(fn ($city) => $city->lat && $city->lng);

        if ($centerCity) {
            $defaultCenter = [(float) $centerCity->lat, (float) $centerCity->lng];
        }

        return view('map', compact(
            'cities',
            'citiesData',
            'currentCity',
            'defaultCenter',
            'routeType',
            'routeLabel'
        ));
    }
}
