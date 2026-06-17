<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_ignores_legacy_two_factor_flag_and_authenticates_directly(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('StrongPass1!'),
            'two_factor_enabled' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'StrongPass1!',
            'remember' => '1',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_removed_security_routes_return_not_found(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/two-factor/verify')->assertNotFound();
    }

    public function test_terms_page_is_available(): void
    {
        $this->get('/terms')
            ->assertOk()
            ->assertSee('Пользовательское соглашение');
    }

    public function test_ui_smoke_pages_do_not_fail(): void
    {
        $city = City::create(['name' => 'Казань', 'lat' => 55.79, 'lng' => 49.12, 'population' => 1000000, 'route_order' => 1]);
        Movie::create(['title' => 'Тестовый фильм', 'genre' => 'Драма', 'duration' => 100, 'age_rating' => '12', 'description' => 'Описание']);
        Setting::create(['current_city_id' => $city->id, 'ticket_price' => 450, 'voting_deadline' => now()->addDay()]);
        $user = User::factory()->create(['city_id' => $city->id]);

        $this->get('/')->assertOk();
        $this->get('/movies')->assertOk();
        $this->get('/afisha')->assertOk();
        $this->actingAs($user)->get('/profile')->assertOk();
    }
}
