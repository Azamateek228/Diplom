<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $tab = $request->input('tab', 'profile');
        if (!in_array($tab, ['profile', 'tickets'], true)) {
            $tab = 'profile';
        }

        $user = auth()->user();
        $cities = City::orderBy('name')->get();
        $tickets = $user->tickets()->with(['city', 'movie'])->latest()->get();

        return view('profile.edit', compact('user', 'cities', 'tickets', 'tab'));
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
