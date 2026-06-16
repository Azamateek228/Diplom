<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class TwoFactorController extends Controller
{
    private const CODE_TTL_MINUTES = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAIL_SETUP_HINT = 'Код записан через резервный log-mailer. Для реальной отправки заполните SMTP и MAIL_FROM_ADDRESS в .env, затем выполните php artisan config:clear.';

    public function showVerify()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login')->withErrors('Сначала необходимо войти в систему');
        }

        return view('auth.two-factor-verify');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login')->withErrors('Сначала необходимо войти в систему');
        }

        $user = User::find(session('2fa_user_id'));
        if (!$user) {
            session()->forget(['2fa_user_id', '2fa_user_email', '2fa_remember', '2fa_last_sent_at']);
            return redirect()->route('login')->withErrors('Пользователь не найден');
        }

        if ($user->verifyTwoFactorCode($request->code)) {
            $remember = (bool) session('2fa_remember', false);
            Auth::login($user, $remember);
            session()->forget(['2fa_user_id', '2fa_user_email', '2fa_remember', '2fa_last_sent_at']);
            $request->session()->regenerate();

            return redirect('/')->with('success', 'Вход подтверждён');
        }

        return back()->withErrors(['code' => 'Неверный код или срок его действия истёк']);
    }

    public function showSettings()
    {
        return view('profile.two-factor-settings');
    }

    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return redirect()->route('two-factor.settings')->with('success', 'Двухфакторная аутентификация уже включена');
        }

        if (!$this->sendCode($user)) {
            return back()->with('error', 'Не удалось подготовить код подтверждения. Подробности записаны в laravel.log.');
        }

        $request->session()->put('2fa_setup_user_id', $user->id);
        $request->session()->put('2fa_setup_email', $user->email);
        $request->session()->put('2fa_setup_last_sent_at', now()->timestamp);

        return view('profile.two-factor-setup');
    }

    public function confirmEnable(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = $request->user();
        if ((int) session('2fa_setup_user_id') !== (int) $user->id) {
            return redirect()->route('two-factor.settings')->with('error', 'Сначала запросите код включения 2FA');
        }

        if ($user->verifyTwoFactorCode($request->code)) {
            $user->update(['two_factor_enabled' => true]);
            $user->twoFactorCodes()->delete();
            $request->session()->forget(['2fa_setup_user_id', '2fa_setup_email', '2fa_setup_last_sent_at']);

            return redirect()->route('profile.edit')->with('success', 'Двухфакторная аутентификация включена');
        }

        return back()->withErrors(['code' => 'Неверный код или срок его действия истёк']);
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required']);

        $user = $request->user();
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Неверный пароль']);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
        $user->twoFactorCodes()->delete();

        return redirect()->route('profile.edit')->with('success', 'Двухфакторная аутентификация отключена');
    }

    public function resend(Request $request)
    {
        $setupUserId = session('2fa_setup_user_id');
        $loginUserId = session('2fa_user_id');
        $sessionKey = $setupUserId ? '2fa_setup_last_sent_at' : '2fa_last_sent_at';
        $userId = $setupUserId ?: $loginUserId;

        if (!$userId) {
            return response()->json(['error' => 'Сессия подтверждения не найдена'], 400);
        }

        $lastSentAt = (int) session($sessionKey, 0);
        $retryAfter = self::RESEND_COOLDOWN_SECONDS - (now()->timestamp - $lastSentAt);
        if ($retryAfter > 0) {
            return response()->json(['error' => "Повторная отправка будет доступна через {$retryAfter} сек.", 'retry_after' => $retryAfter], 429);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        if (!$this->sendCode($user)) {
            return response()->json(['error' => 'Не удалось подготовить код подтверждения. Подробности записаны в laravel.log.'], 500);
        }

        session()->put($sessionKey, now()->timestamp);

        return response()->json(['success' => true, 'message' => 'Код отправлен повторно']);
    }

    private function sendCode(User $user): bool
    {
        $code = $user->generateTwoFactorCode();

        try {
            $mailer = $this->resolveMailDriver();
            Mail::mailer($mailer)->to($user->email)->send(new TwoFactorCodeMail($code, $user->name, self::CODE_TTL_MINUTES));
            return true;
        } catch (\Throwable $e) {
            Log::error('Two-factor mail error', ['user_id' => $user->id, 'message' => $e->getMessage(), 'hint' => self::MAIL_SETUP_HINT]);
            return false;
        }
    }
    private function resolveMailDriver(): string
    {
        $mailer = (string) config('mail.default', 'log');

        if ($mailer === 'smtp' && blank(config('mail.mailers.smtp.host'))) {
            Log::warning('SMTP for 2FA is not configured; using log mailer fallback.');
            return 'log';
        }
    }
    private function ensureMailCanBeSent(): void
    {
        $mailer = (string) config('mail.default');

        return $mailer;
    }

}
