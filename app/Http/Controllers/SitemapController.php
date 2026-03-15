<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\City;
use App\Models\Route as RouteModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Генерирует XML sitemap для поисковых систем
     */
    public function index()
    {
        // Кэшируем sitemap на 1 час для производительности
        $sitemap = Cache::remember('sitemap', 3600, function () {
            $urls = [];

            // Главная страница
            $urls[] = [
                'loc' => url('/'),
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'daily',
                'priority' => '1.0'
            ];

            // Афиша фильмов
            $urls[] = [
                'loc' => route('movies.index'),
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'daily',
                'priority' => '0.9'
            ];

            // Страницы фильмов
            Movie::whereHas('poster')->orWhereNotNull('description')->each(function ($movie) use (&$urls) {
                $urls[] = [
                    'loc' => route('movies.index') . '#movie-' . $movie->id,
                    'lastmod' => $movie->updated_at->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7'
                ];
            });

            // Маршрут на неделю
            $urls[] = [
                'loc' => route('route.index'),
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'daily',
                'priority' => '0.8'
            ];

            // Карта
            $urls[] = [
                'loc' => url('/map'),
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'hourly',
                'priority' => '0.8'
            ];

            // Страницы городов (если есть)
            City::each(function ($city) use (&$urls) {
                $urls[] = [
                    'loc' => route('movies.index', ['city_id' => $city->id]),
                    'lastmod' => $city->updated_at->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6'
                ];
            });

            // Страницы регистрации и входа (с низким приоритетом)
            $urls[] = [
                'loc' => route('login'),
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'monthly',
                'priority' => '0.3'
            ];

            $urls[] = [
                'loc' => route('register'),
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'monthly',
                'priority' => '0.3'
            ];

            return $urls;
        });

        return response()->view('sitemap.index', compact('sitemap'))
            ->header('Content-Type', 'text/xml');
    }
}
