<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = auth()->user()
            ->tickets()
            ->with(['city', 'movie'])
            ->latest()
            ->get();

        return view('tickets.index', compact('tickets'));
    }

    public function create(Request $request)
    {
        $city = City::findOrFail($request->integer('city_id'));
        $movie = Movie::findOrFail($request->integer('movie_id'));
        $showDate = Carbon::parse($request->input('show_date'))->toDateString();
        $showTime = $request->input('show_time', '19:00');
        $ticketPrice = Setting::first()?->ticket_price ?? 350;
        $maxTickets = $this->capacityByCity($city);
        $soldTickets = $this->soldTickets($city->id, $movie->id, $showDate);
        $availableTickets = max(0, $maxTickets - $soldTickets);

        return view('tickets.create', compact(
            'city',
            'movie',
            'showDate',
            'showTime',
            'ticketPrice',
            'maxTickets',
            'soldTickets',
            'availableTickets'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'movie_id' => 'required|exists:movies,id',
            'show_date' => 'required|date',
            'show_time' => 'required|string|max:5',
            'quantity' => 'required|integer|min:1',
            'card_number' => 'required|string|min:16|max:19',
            'card_holder' => 'required|string|max:255',
            'card_expiry' => 'required|string|max:5',
            'card_cvv' => 'required|string|min:3|max:4',
        ]);

        $city = City::findOrFail($validated['city_id']);
        $movie = Movie::findOrFail($validated['movie_id']);
        $showDate = Carbon::parse($validated['show_date'])->toDateString();
        $maxTickets = $this->capacityByCity($city);
        $soldTickets = $this->soldTickets($city->id, $movie->id, $showDate);
        $availableTickets = max(0, $maxTickets - $soldTickets);

        if ($validated['quantity'] > $availableTickets) {
            return back()->withInput()->with('error', 'Недостаточно билетов. Доступно: ' . $availableTickets);
        }

        $unitPrice = Setting::first()?->ticket_price ?? 350;
        $totalPrice = $unitPrice * $validated['quantity'];

        Ticket::create([
            'user_id' => auth()->id(),
            'city_id' => $city->id,
            'movie_id' => $movie->id,
            'show_date' => $showDate,
            'show_time' => $validated['show_time'],
            'quantity' => $validated['quantity'],
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'status' => 'purchased',
            'payment_method' => 'card',
            'payment_reference' => 'PAY-' . strtoupper(Str::random(10)),
            'qr_token' => Str::uuid()->toString(),
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Оплата прошла успешно. Билет добавлен в раздел "Мои билеты" (QR-код).');
    }

    public function refund(Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        if ($ticket->status === 'refunded') {
            return back()->with('error', 'Билет уже возвращён.');
        }

        $ticket->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        return back()->with('success', 'Билет успешно возвращён.');
    }

    private function soldTickets(int $cityId, int $movieId, string $showDate): int
    {
        return (int) Ticket::where('city_id', $cityId)
            ->where('movie_id', $movieId)
            ->whereDate('show_date', $showDate)
            ->where('status', 'purchased')
            ->sum('quantity');
    }

    private function capacityByCity(City $city): int
    {
        $population = (int) ($city->population ?? 40000);

        if ($population <= 20000) {
            return 40;
        }
        if ($population <= 50000) {
            return 60;
        }
        if ($population <= 100000) {
            return 90;
        }

        return 120;
    }
}
