<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CinemaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_vote_for_movie_in_their_city(): void
    {
        [$user, $city, $movie] = $this->demoEntities();

        $response = $this->actingAs($user)->post(route('votes.store'), [
            'movie_id' => $movie->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('votes', [
            'user_id' => $user->id,
            'city_id' => $city->id,
            'movie_id' => $movie->id,
        ]);
    }

    public function test_user_without_city_cannot_vote(): void
    {
        [, , $movie] = $this->demoEntities();
        $user = User::factory()->create(['city_id' => null]);

        $response = $this->actingAs($user)->post(route('votes.store'), [
            'movie_id' => $movie->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('votes', 0);
    }

    public function test_user_can_buy_demo_ticket_with_valid_card(): void
    {
        [$user, $city, $movie] = $this->demoEntities();

        $response = $this->actingAs($user)->post(route('tickets.store'), $this->validPaymentPayload($city, $movie, [
            'quantity' => 2,
        ]));

        $response->assertRedirect(route('profile.edit', ['tab' => 'tickets']));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'city_id' => $city->id,
            'movie_id' => $movie->id,
            'quantity' => 2,
            'total_price' => 900,
            'status' => 'purchased',
            'payment_method' => 'demo-card',
        ]);
    }

    public function test_expired_card_is_rejected(): void
    {
        [$user, $city, $movie] = $this->demoEntities();

        $response = $this->actingAs($user)->from(route('tickets.create', [
            'city_id' => $city->id,
            'movie_id' => $movie->id,
            'show_date' => now()->addDays(7)->toDateString(),
            'show_time' => '19:00',
        ]))->post(route('tickets.store'), $this->validPaymentPayload($city, $movie, [
            'card_expiry' => '01/20',
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors('card_expiry');
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_invalid_card_number_is_rejected(): void
    {
        [$user, $city, $movie] = $this->demoEntities();

        $response = $this->actingAs($user)->post(route('tickets.store'), $this->validPaymentPayload($city, $movie, [
            'card_number' => '4242 4242 4242 4241',
        ]));

        $response->assertSessionHasErrors('card_number');
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_user_cannot_buy_more_tickets_than_available(): void
    {
        [$user, $city, $movie] = $this->demoEntities(['population' => 10000]);
        $this->createTicket($user, $city, $movie, now()->addDays(7)->toDateString(), 39);

        $response = $this->actingAs($user)->post(route('tickets.store'), $this->validPaymentPayload($city, $movie, [
            'quantity' => 2,
        ]));

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_user_can_refund_ticket_more_than_24_hours_before_show(): void
    {
        [$user, $city, $movie] = $this->demoEntities();
        $ticket = $this->createTicket($user, $city, $movie, now()->addDays(3)->toDateString(), 1);

        $response = $this->actingAs($user)->post(route('tickets.refund', $ticket));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'refunded',
        ]);
    }

    public function test_user_cannot_refund_ticket_less_than_24_hours_before_show(): void
    {
        [$user, $city, $movie] = $this->demoEntities();
        $ticket = $this->createTicket($user, $city, $movie, now()->addHours(12)->toDateString(), 1, now()->addHours(12)->format('H:i'));

        $response = $this->actingAs($user)->post(route('tickets.refund', $ticket));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'purchased',
        ]);
    }

    private function demoEntities(array $cityOverrides = []): array
    {
        $city = City::create(array_merge([
            'name' => 'Елабуга',
            'lat' => 55.7567,
            'lng' => 52.0544,
            'population' => 74390,
            'route_order' => 1,
        ], $cityOverrides));

        $movie = Movie::create([
            'title' => 'Лето на Каме',
            'genre' => 'Драма',
            'duration' => 96,
            'age_rating' => '12',
            'description' => 'Тестовый фильм для маршрута.',
            'poster' => 'images/movies/movie-1.svg',
        ]);

        Setting::updateOrCreate(['id' => 1], [
            'current_city_id' => $city->id,
            'ticket_price' => 450,
            'voting_deadline' => now()->addDays(3),
        ]);

        $user = User::factory()->create([
            'city_id' => $city->id,
            'role' => 'user',
        ]);

        return [$user, $city, $movie];
    }

    private function validPaymentPayload(City $city, Movie $movie, array $overrides = []): array
    {
        return array_merge([
            'city_id' => $city->id,
            'movie_id' => $movie->id,
            'show_date' => now()->addDays(7)->toDateString(),
            'show_time' => '19:00',
            'quantity' => 1,
            'card_number' => '4242 4242 4242 4242',
            'card_holder' => 'IVAN IVANOV',
            'card_expiry' => now()->addYears(2)->format('m/y'),
            'card_cvv' => '123',
        ], $overrides);
    }

    private function createTicket(User $user, City $city, Movie $movie, string $showDate, int $quantity, string $showTime = '19:00'): Ticket
    {
        return Ticket::create([
            'user_id' => $user->id,
            'city_id' => $city->id,
            'movie_id' => $movie->id,
            'show_date' => $showDate,
            'show_time' => $showTime,
            'quantity' => $quantity,
            'unit_price' => 450,
            'total_price' => 450 * $quantity,
            'status' => 'purchased',
            'payment_method' => 'demo-card',
            'payment_reference' => 'TEST-' . Str::random(8),
            'qr_token' => (string) Str::uuid(),
        ]);
    }
}
