<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Vote;
use App\Models\City;
use App\Models\Setting;
use App\Models\Ticket;
use App\Support\NearbyCitySelector;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::with(['city', 'votes'])
            ->withCount('votes');

        // Фильтр по городу
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        $movies = $query->orderByDesc('votes_count')->get();
        $soldTicketsByMovie = Ticket::query()
            ->selectRaw('movie_id, SUM(quantity) as sold_total')
            ->where('status', 'purchased')
            ->groupBy('movie_id')
            ->pluck('sold_total', 'movie_id');

        $movies->each(function ($movie) use ($soldTicketsByMovie) {
            $soldTickets = (int) ($soldTicketsByMovie[$movie->id] ?? 0);
            $capacity = (int) ($movie->venue_capacity ?? 0);
            $movie->sold_tickets = $soldTickets;
            $movie->fill_percentage = $capacity > 0
                ? min(100, (int) round(($soldTickets / $capacity) * 100))
                : 0;
        });

        $cities = NearbyCitySelector::mapCities(10, auth()->user()?->city_id);
        $settings = Setting::first();
        $votingDeadline = $settings?->voting_deadline;
        $ticketPrice = $settings?->ticket_price ?? 350;
        $votingClosed = $votingDeadline && now()->greaterThan($votingDeadline);

        $userCityStats = null;
        if (auth()->check() && auth()->user()->city_id) {
            $cityId = auth()->user()->city_id;
            $userCityStats = [
                'city' => City::find($cityId),
                'votes_count' => Vote::where('city_id', $cityId)->count(),
                'expected_attendees' => Vote::where('city_id', $cityId)->sum('expected_attendees'),
            ];
        }

        return view('movies.index', compact('movies', 'cities', 'userCityStats', 'votingDeadline', 'votingClosed', 'ticketPrice'));
    }

    public function show(Movie $movie)
    {
        return view('movies.show', compact('movie'));
    }

    public function create()
    {
        $cities = NearbyCitySelector::mapCities(10);
        return view('movies.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'nullable|string|max:255',
            'duration' => 'required|integer|min:1',
            'age_rating' => 'required|string',
            'description' => 'nullable|string',
            'poster' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'venue' => 'nullable|string|max:255',
            'show_time' => 'nullable|date',
            'venue_capacity' => 'nullable|integer|min:1',
            'expected_attendees' => 'nullable|integer|min:0',
        ]);

        Movie::create($validated);

        return redirect()->route('admin.stats')->with('success', 'Фильм успешно добавлен!');
    }

    public function edit(Movie $movie)
    {
        $cities = NearbyCitySelector::mapCities(10, $movie->city_id);
        return view('movies.edit', compact('movie', 'cities'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'nullable|string|max:255',
            'duration' => 'required|integer|min:1',
            'age_rating' => 'required|string',
            'description' => 'nullable|string',
            'poster' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'venue' => 'nullable|string|max:255',
            'show_time' => 'nullable|date',
            'venue_capacity' => 'nullable|integer|min:1',
            'expected_attendees' => 'nullable|integer|min:0',
        ]);

        $movie->update($validated);

        return redirect()->route('admin.stats')->with('success', 'Фильм успешно обновлен!');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();

        return redirect()->route('admin.stats')->with('success', 'Фильм успешно удален!');
    }
}
