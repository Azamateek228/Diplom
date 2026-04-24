<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request, Movie $movie)
    {
        $city = $movie->city;

        if (!$city) {
            return back()->with('error', 'Нельзя купить билет: у фильма не указан город.');
        }

        $maxTickets = $city->ticketLimit();

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $maxTickets],
        ]);

        $soldTickets = Ticket::where('movie_id', $movie->id)
            ->where('city_id', $city->id)
            ->whereNull('refunded_at')
            ->sum('quantity');

        if (($soldTickets + $validated['quantity']) > $maxTickets) {
            $available = max(0, $maxTickets - $soldTickets);

            return back()->with('error', "Осталось только {$available} билетов для {$city->name}.");
        }

        $price = $movie->ticket_price;
        $total = $price * $validated['quantity'];

        Ticket::create([
            'user_id' => auth()->id(),
            'movie_id' => $movie->id,
            'city_id' => $city->id,
            'quantity' => $validated['quantity'],
            'price_per_ticket' => $price,
            'total_amount' => $total,
        ]);

        return back()->with('success', "Покупка успешна: {$validated['quantity']} бил. на сумму {$total} ₽.");
    }

    public function refund(Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        if ($ticket->refunded_at) {
            return back()->with('error', 'Этот билет уже возвращён.');
        }

        $ticket->update(['refunded_at' => now()]);

        return back()->with('success', 'Билет возвращён. Средства отправлены на выбранный способ оплаты.');
    }
}
