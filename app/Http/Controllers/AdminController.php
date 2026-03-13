<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vote;
use App\Models\Movie;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function stats(Request $request)
    {
        $usersCount = User::count();
        $votesCount = Vote::count();

        $topMovie = Movie::withCount('votes')
            ->orderByDesc('votes_count')
            ->first();

        $topCity = City::withCount('votes')
            ->orderByDesc('votes_count')
            ->first();

        // Получаем все города для фильтра
        $cities = City::orderBy('name')->get();
        
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
            $setting->update(['current_city_id' => $request->input('current_city_id') ?: null]);
            return redirect()->route('admin.stats')->with('success', 'Текущий город обновлён.');
        }

        $currentCityId = Setting::first()?->current_city_id;

        return view('admin.stats', compact(
            'usersCount',
            'votesCount',
            'topMovie',
            'topCity',
            'cities',
            'movies',
            'selectedCityId',
            'cityStats',
            'currentCityId'
        ));
    }
}
