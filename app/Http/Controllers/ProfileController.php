<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $cities = City::orderBy('name')->get();

        return view('profile.edit', compact('user', 'cities'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'nullable|exists:cities,id',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Профиль обновлён.');
    }
}
