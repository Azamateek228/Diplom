<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\City;
use App\Models\Vote;

class VoteController extends Controller
{
    private const VOTING_DEADLINE = '2026-05-31 23:59:59';

    public function index()
    {
        $cities = City::all();
        $movies = Movie::withCount('votes')->get();

        return view('votes.index', compact('cities', 'movies'));
    }

    public function store(Request $request)
    {
        if (now()->gt(self::VOTING_DEADLINE)) {
            return redirect()->back()->with('error', 'Голосование завершено ' . \Carbon\Carbon::parse(self::VOTING_DEADLINE)->format('d.m.Y H:i'));
        }

        $user = auth()->user();
        $movie = Movie::findOrFail($request->movie_id);

        if ($movie->city_id) {
            $cityId = $movie->city_id;
        } else {
            $cityId = $request->city_id ?: $user->city_id;
        }

        if (!$cityId) {
            return redirect()->back()->with('error', 'Необходимо указать город для голосования. Пожалуйста, выберите город в форме голосования.');
        }

        $expectedAttendees = (int) $request->input('expected_attendees', 1);
        $expectedAttendees = max(1, min(10, $expectedAttendees));

        $existingVote = Vote::where('user_id', $user->id)
            ->where('city_id', $cityId)
            ->first();

        if ($existingVote) {
            $existingVote->update([
                'movie_id' => $movie->id,
                'expected_attendees' => $expectedAttendees,
            ]);
            return redirect()->back()->with('success', 'Ваш голос обновлён!');
        }

        Vote::create([
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'city_id' => $cityId,
            'expected_attendees' => $expectedAttendees,
        ]);

        return redirect()->back()->with('success', 'Голос учтён!');
    }
}
