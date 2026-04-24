<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Vote;
use App\Models\City;
use App\Models\Ticket;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    private const VOTING_DEADLINE = '2026-05-31 23:59:59';

    public function index(Request $request)
    {
        $query = Movie::with(['city', 'votes'])
            ->withCount('votes');

        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        $movies = $query
            ->orderByRaw('show_at IS NULL, show_at ASC')
            ->orderBy('city_id')
            ->orderBy('title')
            ->get();

        $cities = City::orderBy('name')->get();

        $userCityStats = null;
        if (auth()->check() && auth()->user()->city_id) {
            $cityId = auth()->user()->city_id;
            $userCityStats = [
                'city' => City::find($cityId),
                'votes_count' => Vote::where('city_id', $cityId)->count(),
                'expected_attendees' => Vote::where('city_id', $cityId)->sum('expected_attendees'),
            ];
        }

        $soldByMovie = Ticket::whereNull('refunded_at')
            ->selectRaw('movie_id, SUM(quantity) as sold')
            ->groupBy('movie_id')
            ->pluck('sold', 'movie_id');

        return view('movies.index', compact('movies', 'cities', 'userCityStats', 'soldByMovie'));
    }

    public function create()
    {
        return view('movies.create');
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
            'expected_attendees' => 'nullable|integer|min:0',
            'show_at' => 'nullable|date',
            'ticket_price' => 'required|integer|min:1',
        ]);

        Movie::create($validated);

        return redirect()->route('admin.stats')->with('success', 'Фильм успешно добавлен!');
    }

    public function edit(Movie $movie)
    {
        $cities = City::orderBy('name')->get();
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
            'expected_attendees' => 'nullable|integer|min:0',
            'show_at' => 'nullable|date',
            'ticket_price' => 'required|integer|min:1',
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
