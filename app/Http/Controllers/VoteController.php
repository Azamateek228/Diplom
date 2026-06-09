<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Setting;
use App\Models\Vote;
use App\Support\NearbyCitySelector;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index()
    {
        $cities = NearbyCitySelector::mapCities(10, auth()->user()?->city_id);
        $movies = Movie::withCount('votes')->get();

        return view('votes.index', compact('cities', 'movies'));
    }

    public function store(Request $request)
    {
        $votingDeadline = Setting::first()?->voting_deadline;
        if ($votingDeadline && now()->greaterThan($votingDeadline)) {
            return redirect()->back()->with('error', 'Голосование завершено: дедлайн был ' . $votingDeadline->format('d.m.Y H:i'));
        }

        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
        ]);

        $user = auth()->user();
        $cityId = $user->city_id;

        if (!$cityId) {
            return redirect()->back()->with('error', 'Сначала выберите свой город в профиле.');
        }

        $movie = Movie::findOrFail($validated['movie_id']);

        Vote::updateOrCreate(
            ['user_id' => $user->id, 'city_id' => $cityId],
            ['movie_id' => $movie->id, 'expected_attendees' => 1]
        );

        return redirect()->back()->with('success', 'Ваш голос учтён. Он повлияет на маршрут выездного кинотеатра.');
    }
}
