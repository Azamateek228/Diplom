<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AfishaController extends Controller
{
    public function index()
    {
        $cities = City::withCount('votes')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->orderByDesc('votes_count')
            ->limit(10)
            ->get();

        $fallbackMovie = Movie::withCount('votes')->orderByDesc('votes_count')->first();
        $winnersByCity = $this->resolveWinnersByCity($cities, $fallbackMovie);
        $routeCities = $this->buildRouteOrder($cities, Setting::first()?->current_city_id);
        $ticketPrice = Setting::first()?->ticket_price ?? 350;
        $weeklySchedule = $this->buildWeeklySchedule($routeCities, $winnersByCity, $fallbackMovie);

        return view('afisha.index', compact('weeklySchedule', 'routeCities', 'winnersByCity', 'ticketPrice'));
    }

    private function resolveWinnersByCity(Collection $cities, ?Movie $fallbackMovie): array
    {
        $winners = [];

        foreach ($cities as $city) {
            $winnerVote = Vote::selectRaw('movie_id, COUNT(*) as votes_count, COALESCE(SUM(expected_attendees), 0) as expected_sum')
                ->where('city_id', $city->id)
                ->groupBy('movie_id')
                ->orderByDesc('votes_count')
                ->orderByDesc('expected_sum')
                ->first();

            $movie = null;
            if ($winnerVote) {
                $movie = Movie::find($winnerVote->movie_id);
            }

            if (!$movie) {
                $movie = Movie::where('city_id', $city->id)
                    ->withCount('votes')
                    ->orderByDesc('votes_count')
                    ->first();
            }

            $winners[$city->id] = $movie ?: $fallbackMovie;
        }

        return $winners;
    }

    private function buildRouteOrder(Collection $cities, ?int $currentCityId): array
    {
        if ($cities->isEmpty()) {
            return [];
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
                $distance = $this->distance(
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

        return $ordered;
    }

    private function buildWeeklySchedule(array $routeCities, array $winnersByCity, ?Movie $fallbackMovie): array
    {
        $schedule = [];
        $baseDate = Carbon::today();

        if (empty($routeCities)) {
            return $schedule;
        }

        for ($day = 0; $day < 7; $day++) {
            $city = $routeCities[$day % count($routeCities)];
            $movie = $winnersByCity[$city->id] ?? $fallbackMovie;
            $date = $baseDate->copy()->addDays($day);

            $showTime = $movie?->show_time
                ? Carbon::parse($movie->show_time)->format('H:i')
                : '19:00';

            $schedule[] = [
                'date' => $date->translatedFormat('d.m.Y, l'),
                'show_date' => $date->toDateString(),
                'city' => $city,
                'movie' => $movie,
                'show_time' => $showTime,
                'capacity' => $this->capacityByCity($city),
                'sold' => $movie ? $this->soldTickets($city->id, $movie->id, $date->toDateString()) : 0,
            ];
        }

        return $schedule;
    }

    private function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function soldTickets(int $cityId, int $movieId, string $showDate): int
    {
        return (int) Ticket::where('city_id', $cityId)
            ->where('movie_id', $movieId)
            ->whereDate('show_date', $showDate)
            ->where('status', 'purchased')
            ->sum('quantity');
    }

    private function capacityByCity(City $city): int
    {
        $population = (int) ($city->population ?? 40000);

        if ($population <= 20000) {
            return 40;
        }
        if ($population <= 50000) {
            return 60;
        }
        if ($population <= 100000) {
            return 90;
        }

        return 120;
    }
}
