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
        $cities = NearbyCitySelector::actualRoute();
        $routeType = NearbyCitySelector::actualRouteType();
        $routeLabel = NearbyCitySelector::actualRouteLabel();

        $fallbackMovie = Movie::withCount('votes')->orderByDesc('votes_count')->orderBy('title')->first();
        $winnersByCity = $this->resolveWinnersByCity($cities, $fallbackMovie);
        $routeCities = $cities->all();
        $ticketPrice = $settings?->ticket_price ?? 350;
        $weeklySchedule = $this->buildSchedule($routeCities, $winnersByCity, $fallbackMovie, $settings?->voting_deadline);

        return view('afisha.index', compact(
            'weeklySchedule',
            'routeCities',
            'winnersByCity',
            'ticketPrice',
            'routeType',
            'routeLabel'
        ));
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
                ->orderBy('movie_id')
                ->first();

            $winners[$city->id] = $winnerVote
                ? Movie::find($winnerVote->movie_id) ?: $fallbackMovie
                : $fallbackMovie;
        }

        return $winners;
    }

    private function buildSchedule(array $routeCities, array $winnersByCity, ?Movie $fallbackMovie, ?Carbon $votingDeadline): array
    {
        $schedule = [];
        $baseDate = $this->scheduleStartDate($votingDeadline);

        foreach ($routeCities as $day => $city) {
            $movie = $winnersByCity[$city->id] ?? $fallbackMovie;
            $date = $baseDate->copy()->addDays($day);
            $startAt = $date->copy()->setTime(19, 0);
            $duration = (int) ($movie?->duration ?? 90);
            $endAt = $startAt->copy()->addMinutes($duration);

            $schedule[] = [
                'date' => $date->translatedFormat('d.m.Y, l'),
                'show_date' => $date->toDateString(),
                'city' => $city,
                'movie' => $movie,
                'show_time' => $startAt->format('H:i'),
                'end_time' => $endAt->format('H:i'),
                'venue' => $this->defaultVenueForCity($city),
                'capacity' => $this->capacityByCity($city),
                'sold' => $movie ? $this->soldTickets($city->id, $movie->id, $date->toDateString()) : 0,
                'city_votes' => (int) ($city->votes_count ?? Vote::where('city_id', $city->id)->count()),
            ];
        }

        return $schedule;
    }


    private function scheduleStartDate(?Carbon $votingDeadline): Carbon
    {
        $minimumStartDate = Carbon::today()->addDays(3);

        if (! $votingDeadline) {
            return $minimumStartDate;
        }

        $afterDeadline = $votingDeadline->copy()->addDay()->startOfDay();

        return $afterDeadline->greaterThan($minimumStartDate)
            ? $afterDeadline
            : $minimumStartDate;
    }

    private function defaultVenueForCity(City $city): string
    {
        $venues = [
            'Набережные Челны' => 'Площадь Азатлык',
            'Нижнекамск' => 'Парк Нефтехимиков',
            'Казань' => 'Центральная площадь',
            'Елабуга' => 'Набережная',
            'Альметьевск' => 'Городской парк',
        ];

        if (isset($venues[$city->name])) {
            return $venues[$city->name];
        }

        $defaults = ['Центральная площадь', 'Городской парк', 'Дом культуры', 'Набережная'];

        return $defaults[((int) $city->id) % count($defaults)];
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
