<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\City;
use App\Models\Setting;
use App\Models\Vote;

class VoteController extends Controller
{
    public function index()
    {
        $cities = City::all();
        $movies = Movie::withCount('votes')->get();

        return view('votes.index', compact('cities', 'movies'));
    }

    public function store(Request $request)
    {
        $votingDeadline = Setting::first()?->voting_deadline;
        if ($votingDeadline && now()->greaterThan($votingDeadline)) {
            return redirect()->back()->with('error', 'Голосование завершено: дедлайн был ' . $votingDeadline->format('d.m.Y H:i'));
        }

        $user = auth()->user();
        $movie = Movie::findOrFail($request->movie_id);
        
        // Определяем city_id с жёсткими правилами:
        // 1) если у фильма уже задан город — голос всегда идёт в этот город (игнорируем то, что пришло из формы)
        // 2) если у фильма нет города — допускаем выбор города в форме, либо берём город пользователя
        if ($movie->city_id) {
            // Фильм привязан к конкретному городу — фиксируем голос только за этот город,
            // чтобы нельзя было «привязать» фильм к произвольному городу через форму
            $cityId = $movie->city_id;
        } else {
            // Фильм без города — позволяем выбрать город вручную
            $cityId = $request->city_id ?: $user->city_id;
        }
        
        if (!$cityId) {
            return redirect()->back()->with('error', 'Необходимо указать город для голосования. Пожалуйста, выберите город в форме голосования.');
        }

        $expectedAttendees = (int) $request->input('expected_attendees', 1);
        $expectedAttendees = max(1, min(10, $expectedAttendees));
        
        // Проверяем, не голосовал ли уже пользователь в этом городе
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

