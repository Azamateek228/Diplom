<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vote;
use App\Support\NearbyCitySelector;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function stats(Request $request)
    {
        $settings = Setting::first();
        $currentCityId = $settings?->current_city_id;
        $selectedCityId = $request->get('city_id');
        $cities = NearbyCitySelector::fullRoute();
        $routeCities = NearbyCitySelector::actualRoute();
        $routeType = NearbyCitySelector::actualRouteType();
        $routeLabel = NearbyCitySelector::actualRouteLabel();

        $usersCount = User::count();
        $votesCount = Vote::count();
        $ticketsPurchased = (int) Ticket::where('status', 'purchased')->sum('quantity');
        $totalCapacity = max(1, $routeCities->sum(fn ($city) => $this->capacityByCity($city)));
        $overallLoadPercent = min(100, (int) round(($ticketsPurchased / $totalCapacity) * 100));

        $topMovie = Movie::withCount('votes')
            ->orderByDesc('votes_count')
            ->orderBy('title')
            ->first();

        $topCity = City::withCount('votes')
            ->orderByDesc('votes_count')
            ->orderBy('name')
            ->first();

        $moviesQuery = Movie::withCount('votes');

        if ($selectedCityId) {
            $moviesQuery->whereHas('votes', fn ($query) => $query->where('city_id', $selectedCityId));
        }

        $movies = $moviesQuery->orderByDesc('votes_count')->orderBy('title')->get();
        $movies->each(function (Movie $movie) {
            $movie->session_status_label = 'Каталог';
            $movie->session_status_class = 'status-planned';
        });

        $upcomingSessions = collect();

        $cityStats = $cities->map(function ($city) {
            $totalVotes = Vote::where('city_id', $city->id)->count();
            $totalExpected = Vote::where('city_id', $city->id)->sum('expected_attendees');

            return [
                'city' => $city,
                'movies_count' => Vote::where('city_id', $city->id)->distinct('movie_id')->count('movie_id'),
                'total_expected' => $totalExpected,
                'total_votes' => $totalVotes,
            ];
        });

        $cityWinners = $routeCities->map(function ($city) {
            $winnerVote = Vote::selectRaw('movie_id, COUNT(*) as votes_count')
                ->where('city_id', $city->id)
                ->groupBy('movie_id')
                ->orderByDesc('votes_count')
                ->orderBy('movie_id')
                ->first();

            return [
                'city' => $city,
                'movie' => $winnerVote ? Movie::find($winnerVote->movie_id) : null,
                'votes_count' => (int) ($winnerVote->votes_count ?? 0),
            ];
        });

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'current_city_id' => 'nullable|exists:cities,id',
                'voting_deadline' => 'nullable|date',
                'ticket_price' => 'nullable|integer|min:100|max:5000',
            ]);

            if ($request->has('auto_city')) {
                $topByAttendees = $cityStats->sortByDesc('total_expected')->first();
                if ($topByAttendees && $topByAttendees['total_expected'] > 0) {
                    $setting = Setting::firstOrCreate([]);
                    $setting->update(['current_city_id' => $topByAttendees['city']->id]);
                    return redirect()->route('admin.stats')->with('success', 'Текущий город установлен автоматически: ' . $topByAttendees['city']->name);
                }
                return redirect()->route('admin.stats')->with('error', 'Нет данных по городам для автоматического выбора.');
            }
            $setting = Setting::firstOrCreate([]);
            $setting->update([
                'current_city_id' => $validated['current_city_id'] ?? null,
                'voting_deadline' => $validated['voting_deadline'] ?? $setting->voting_deadline,
                'ticket_price' => $validated['ticket_price'] ?? $setting->ticket_price,
            ]);
            return redirect()->route('admin.stats')->with('success', 'Настройки обновлены.');
        }

        $votingDeadline = $settings?->voting_deadline;
        $ticketPrice = $settings?->ticket_price ?? 350;
        $currentCityName = $currentCityId ? $cities->firstWhere('id', $currentCityId)?->name : null;

        return view('admin.stats', compact(
            'usersCount',
            'votesCount',
            'ticketsPurchased',
            'overallLoadPercent',
            'topMovie',
            'topCity',
            'cities',
            'movies',
            'selectedCityId',
            'cityStats',
            'currentCityId',
            'currentCityName',
            'votingDeadline',
            'ticketPrice',
            'upcomingSessions',
            'routeCities',
            'routeType',
            'routeLabel',
            'cityWinners'
        ));
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
