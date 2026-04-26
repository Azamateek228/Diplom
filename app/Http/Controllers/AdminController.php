<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vote;
use App\Models\Movie;
use App\Models\City;
use App\Models\Setting;
use App\Models\Ticket;
use App\Support\NearbyCitySelector;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function stats(Request $request)
    {
        $usersCount = User::count();
        $votesCount = Vote::count();
        $ticketsPurchased = (int) Ticket::where('status', 'purchased')->sum('quantity');
        $totalCapacity = (int) Movie::whereNotNull('venue_capacity')->sum('venue_capacity');
        $overallLoadPercent = $totalCapacity > 0
            ? min(100, (int) round(($ticketsPurchased / $totalCapacity) * 100))
            : 0;

        $topMovie = Movie::withCount('votes')
            ->orderByDesc('votes_count')
            ->first();

        $topCity = City::withCount('votes')
            ->orderByDesc('votes_count')
            ->first();

        // Фильтр по городу
        $selectedCityId = $request->get('city_id');
        $moviesQuery = Movie::with(['city', 'votes'])->withCount('votes');
        
        if ($selectedCityId) {
            $moviesQuery->where('city_id', $selectedCityId);
        }
        
        $movies = $moviesQuery->orderByDesc('votes_count')->get();
        
        // Подсчет по городам: голоса и ожидаемые зрители из таблицы votes
        $cityStats = City::all()->map(function ($city) {
            $cityMovies = Movie::where('city_id', $city->id)->get();
            $totalVotes = Vote::where('city_id', $city->id)->count();
            $totalExpected = Vote::where('city_id', $city->id)->sum('expected_attendees');
            
            return [
                'city' => $city,
                'movies_count' => $cityMovies->count(),
                'total_expected' => $totalExpected,
                'total_votes' => $totalVotes,
            ];
        });

        // Установка текущего города кинотеатра
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

        $settings = Setting::first();
        $currentCityId = $settings?->current_city_id;
        $ensureCityId = $selectedCityId ?: $currentCityId;
        $cities = NearbyCitySelector::naberezhnyeChelnyWithNearest(10, $ensureCityId ? (int) $ensureCityId : null);
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
            'ticketPrice'
        ));
    }
}
