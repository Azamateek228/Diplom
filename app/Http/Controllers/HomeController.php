<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Support\NearbyCitySelector;

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

        $routeCities = NearbyCitySelector::mapCities(10);

        return view('home', compact('upcomingMovies', 'routeCities'));
    }
}
