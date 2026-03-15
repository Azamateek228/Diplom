<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\City;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    /**
     * Показать маршрут на неделю
     */
    public function index()
    {
        // Получаем маршруты на неделю, сгруппированные по дням
        $routes = Route::with(['city', 'movie'])
            ->orderBy('visit_date')
            ->get()
            ->groupBy('day_of_week');

        // Порядок дней недели
        $daysOrder = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
        
        // Сортируем дни
        $sortedRoutes = collect([]);
        foreach ($daysOrder as $day) {
            if ($routes->has($day)) {
                $sortedRoutes->put($day, $routes[$day]);
            }
        }

        return view('route.index', compact('sortedRoutes'));
    }

    /**
     * Админка: управление маршрутом
     */
    public function admin()
    {
        $routes = Route::with(['city', 'movie'])
            ->orderBy('visit_date')
            ->get();
        
        $cities = City::all();
        $movies = Movie::all();

        return view('route.admin', compact('routes', 'cities', 'movies'));
    }

    /**
     * Добавить маршрут
     */
    public function store(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'movie_id' => 'required|exists:movies,id',
            'visit_date' => 'required|date',
            'day_of_week' => 'required|string',
            'venue' => 'nullable|string|max:255',
            'show_time' => 'nullable|date_format:H:i',
        ]);

        Route::create($request->all());

        return redirect()->back()->with('success', 'Маршрут добавлен!');
    }

    /**
     * Обновить маршрут
     */
    public function update(Request $request, $id)
    {
        $route = Route::findOrFail($id);

        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'movie_id' => 'required|exists:movies,id',
            'visit_date' => 'required|date',
            'day_of_week' => 'required|string',
            'venue' => 'nullable|string|max:255',
            'show_time' => 'nullable|date_format:H:i',
        ]);

        $route->update($request->all());

        return redirect()->back()->with('success', 'Маршрут обновлён!');
    }

    /**
     * Удалить маршрут
     */
    public function destroy($id)
    {
        $route = Route::findOrFail($id);
        $route->delete();

        return redirect()->back()->with('success', 'Маршрут удалён!');
    }
}
