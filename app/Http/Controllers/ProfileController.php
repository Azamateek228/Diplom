<?php

namespace App\Http\Controllers;

use App\Support\NearbyCitySelector;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $tab = $request->input('tab', 'profile');
        if (!in_array($tab, ['profile', 'tickets'], true)) {
            $tab = 'profile';
        }

        $user = auth()->user();
        $cities = NearbyCitySelector::naberezhnyeChelnyWithNearest(10, $user->city_id);
        $tickets = $user->tickets()->with(['city', 'movie'])->latest()->get();

        return view('profile.edit', compact('user', 'cities', 'tickets', 'tab'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'nullable|exists:cities,id',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Профиль обновлён.');
    }

    private function citiesForNaberezhnyeChelnyProfile()
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
            return $allCities->sortBy('name')->values();
        }

        return collect([$baseCity])
            ->concat(
                $allCities
                    ->reject(fn ($city) => (int) $city->id === (int) $baseCity->id)
                    ->sortBy(fn ($city) => $this->distanceInKm(
                        (float) $baseCity->lat,
                        (float) $baseCity->lng,
                        (float) $city->lat,
                        (float) $city->lng
                    ))
                    ->take(10)
            )
            ->sortBy('name')
            ->values();
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
