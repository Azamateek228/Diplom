<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Mail\TwoFactorCodeMail;
use App\Models\City;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_two_factor_without_showing_code(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('two-factor.enable'));

        $response->assertOk();
        $response->assertDontSee($user->twoFactorCodes()->first()->code, false);
        Mail::assertSent(TwoFactorCodeMail::class);
    }

    public function test_confirming_valid_two_factor_code_enables_two_factor(): void
    {
        $user = User::factory()->create();
        $code = $user->generateTwoFactorCode();

        $response = $this->actingAs($user)
            ->withSession(['2fa_setup_user_id' => $user->id])
            ->post(route('two-factor.confirm'), ['code' => $code]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertTrue($user->fresh()->two_factor_enabled);
        $this->assertDatabaseCount('two_factor_codes', 0);
    }

    public function test_login_with_two_factor_sends_code_and_waits_for_verification(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'password' => Hash::make('StrongPass1!'),
            'two_factor_enabled' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'StrongPass1!',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('two-factor.verify'));
        $this->assertGuest();
        Mail::assertSent(TwoFactorCodeMail::class);

        $code = $user->twoFactorCodes()->first()->code;
        $verify = $this->withSession(['2fa_user_id' => $user->id, '2fa_remember' => true])
            ->post(route('two-factor.verify'), ['code' => $code]);

        $verify->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_request_sends_email(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        Mail::assertSent(ResetPasswordMail::class);
    }

    public function test_valid_password_reset_token_changes_password_and_deletes_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass1!')]);
        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewPass1!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_invalid_password_reset_token_does_not_change_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPass1!')]);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('valid-token'),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => 'wrong-token',
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ]);

        $response->assertSessionHasErrors('token');
        $this->assertTrue(Hash::check('OldPass1!', $user->fresh()->password));
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
