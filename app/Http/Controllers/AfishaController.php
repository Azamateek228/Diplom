<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Vote;
use App\Support\NearbyCitySelector;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AfishaController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        $cities = NearbyCitySelector::mapCities(10, $settings?->current_city_id);

        $fallbackMovie = Movie::withCount('votes')->orderByDesc('votes_count')->first();
        $winnersByCity = $this->resolveWinnersByCity($cities, $fallbackMovie);
        $routeCities = NearbyCitySelector::orderedRoute($cities, $settings?->current_city_id)->all();
        $ticketPrice = $settings?->ticket_price ?? 350;
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

    private function soldTickets(int $cityId, int $movieId, string $showDate): int
    {
        $soldForDate = Ticket::where('city_id', $cityId)
            ->where('movie_id', $movieId)
            ->whereDate('show_date', $showDate)
            ->where('status', 'purchased')
            ->sum('quantity');

        if ($soldForDate > 0) {
            return (int) $soldForDate;
        }

        return (int) Ticket::where('city_id', $cityId)
            ->where('movie_id', $movieId)
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
