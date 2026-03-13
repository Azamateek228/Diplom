<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::all();
        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        return view('cities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        City::create($validated);
        return redirect()->route('cities.index')->with('success', 'Город успешно добавлен!');
    }

    public function edit(City $city)
    {
        return view('cities.edit', compact('city'));
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        $city->update($validated);
        return redirect()->route('cities.index')->with('success', 'Город успешно обновлен!');
    }

    public function destroy(City $city)
    {
        // Проверяем, есть ли связанные записи
        if ($city->votes()->count() > 0) {
            return redirect()->route('cities.index')
                ->with('error', 'Нельзя удалить город, так как есть голоса, связанные с этим городом.');
        }

        $city->delete();
        return redirect()->route('cities.index')->with('success', 'Город успешно удален!');
    }
}