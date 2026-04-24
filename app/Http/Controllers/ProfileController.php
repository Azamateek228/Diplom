<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Ticket;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $cities = City::orderBy('name')->get();
        $tickets = Ticket::with(['movie', 'city'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('profile.edit', compact('user', 'cities', 'tickets'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'nullable|exists:cities,id',
            'payment_method' => 'nullable|in:card_qr,pdf_invoice',
            'payment_qr_url' => 'nullable|url|max:2048',
            'payment_pdf_url' => 'nullable|url|max:2048',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Профиль обновлён.');
    }
}
