<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Vote;
use App\Models\City;
use App\Models\Setting;
use App\Models\Ticket;
use App\Support\NearbyCitySelector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::withCount('votes');


        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($request->filled('genre')) {
            $query->where('genre', $request->input('genre'));
        }

        if ($request->filled('age_rating')) {
            $query->where('age_rating', $request->input('age_rating'));
        }

        $movies = $query->orderByDesc('votes_count')
            ->orderBy('title')
            ->paginate(8)
            ->withQueryString();
        $soldTicketsByMovie = Ticket::query()
            ->selectRaw('movie_id, SUM(quantity) as sold_total')
            ->where('status', 'purchased')
            ->groupBy('movie_id')
            ->pluck('sold_total', 'movie_id');

        $movies->getCollection()->each(function ($movie) use ($soldTicketsByMovie) {
            $soldTickets = (int) ($soldTicketsByMovie[$movie->id] ?? 0);
            $movie->sold_tickets = $soldTickets;
            $movie->fill_percentage = 0;
        });

        $cities = NearbyCitySelector::fullRoute();
        $genres = Movie::query()->whereNotNull('genre')->distinct()->orderBy('genre')->pluck('genre');
        $ageRatings = Movie::query()->whereNotNull('age_rating')->distinct()->orderBy('age_rating')->pluck('age_rating');
        $filters = $request->only(['search', 'genre', 'age_rating']);
        $activeFiltersCount = collect($filters)->filter(fn ($value) => filled($value))->count();
        $settings = Setting::first();
        $votingDeadline = $settings?->voting_deadline;
        $ticketPrice = $settings?->ticket_price ?? 350;
        $votingClosed = $votingDeadline && now()->greaterThan($votingDeadline);

        $userCityStats = null;
        $userVoteMovieId = null;
        if (auth()->check() && auth()->user()->city_id) {
            $cityId = auth()->user()->city_id;
            $userCityStats = [
                'city' => City::find($cityId),
                'votes_count' => Vote::where('city_id', $cityId)->count(),
                'expected_attendees' => Vote::where('city_id', $cityId)->sum('expected_attendees'),
            ];
            $userVoteMovieId = Vote::where('user_id', auth()->id())->where('city_id', $cityId)->value('movie_id');
        }

        return view('movies.index', compact('movies', 'cities', 'genres', 'ageRatings', 'filters', 'activeFiltersCount', 'userCityStats', 'userVoteMovieId', 'votingDeadline', 'votingClosed', 'ticketPrice'));
    }

    public function show(Movie $movie)
    {
        $movie->loadCount('votes');

        $soldTickets = (int) Ticket::where('movie_id', $movie->id)
            ->where('status', 'purchased')
            ->sum('quantity');
        $fillPercentage = 0;
        $ticketPrice = Setting::first()?->ticket_price ?? 350;
        $votingDeadline = Setting::first()?->voting_deadline;
        $votingClosed = $votingDeadline && now()->greaterThan($votingDeadline);
        $userVoteMovieId = null;

        if (auth()->check() && auth()->user()->city_id) {
            $userVoteMovieId = Vote::where('user_id', auth()->id())
                ->where('city_id', auth()->user()->city_id)
                ->value('movie_id');
        }

        return view('movies.show', compact('movie', 'soldTickets', 'fillPercentage', 'ticketPrice', 'votingClosed', 'userVoteMovieId'));
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
            'poster_file' => 'nullable|image|max:4096',
            'city_id' => 'nullable|exists:cities,id',
            'venue' => 'nullable|string|max:255',
            'show_time' => 'nullable|date',
            'venue_capacity' => 'nullable|integer|min:1',
            'expected_attendees' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('poster_file')) {
            $validated['poster'] = $request->file('poster_file')->store('posters', 'public');
        }

        unset($validated['poster_file']);
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
            'poster_file' => 'nullable|image|max:4096',
            'city_id' => 'nullable|exists:cities,id',
            'venue' => 'nullable|string|max:255',
            'show_time' => 'nullable|date',
            'venue_capacity' => 'nullable|integer|min:1',
            'expected_attendees' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('poster_file')) {
            if ($movie->poster) {
                Storage::disk('public')->delete($movie->poster);
            }

            $validated['poster'] = $request->file('poster_file')->store('posters', 'public');
        }

        unset($validated['poster_file']);
        $movie->update($validated);

        return redirect()->route('admin.stats')->with('success', 'Фильм успешно обновлен!');
    }

    public function updateShowTime(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'show_time' => 'required|date',
        ]);

        $movie->update([
            'show_time' => $validated['show_time'],
        ]);

        return back()->with('success', 'Дата показа обновлена.');
    }

    public function duplicate(Movie $movie)
    {
        $clone = $movie->replicate();
        $clone->title = $movie->title . ' (копия)';
        $clone->show_time = now()->addWeek();
        $clone->save();

        return redirect()->route('movies.edit', $clone)->with('success', 'Фильм продублирован. Проверьте данные и сохраните.');
    }

    public function destroy(Movie $movie)
    {
        if ($movie->poster) {
            Storage::disk('public')->delete($movie->poster);
        }

        $movie->delete();

        return redirect()->route('admin.stats')->with('success', 'Фильм успешно удален!');
    }
}
