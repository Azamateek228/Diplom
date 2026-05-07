<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Movie;

class HomeController extends Controller
{
    public function index()
    {
        $upcomingMovies = Movie::with('city')
            ->withCount('votes')
            ->whereNotNull('show_time')
            ->orderBy('show_time')
            ->take(4)
            ->get();

        $routeCities = City::orderBy('id')->take(11)->get();

        return view('home', compact('upcomingMovies', 'routeCities'));
    }
}
