<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Support\NearbyCitySelector;

class HomeController extends Controller
{
    public function index()
    {
        $upcomingMovies = Movie::withCount('votes')
            ->orderByDesc('votes_count')
            ->orderBy('title')
            ->take(4)
            ->get();

        $routeCities = NearbyCitySelector::actualRoute();

        return view('home', compact('upcomingMovies', 'routeCities'));
    }
}
