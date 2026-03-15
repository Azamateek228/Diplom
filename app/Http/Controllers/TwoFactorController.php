<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TwoFactorController extends Controller
{
    /**
     * Показать форму ввода 2FA-кода при входе
     */
    public function showVerify()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login')
                ->withErrors('Сначала необходимо войти в систему');
        }

        return view('auth.two-factor-verify');
    }

    /**
     * Проверка 2FA-кода при входе
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login')
                ->withErrors('Сначала необходимо войти в систему');
        }

        $user = User::find(session('2fa_user_id'));

        if (!$user) {
            session()->forget('2fa_user_id');
            return redirect()->route('login')
                ->withErrors('Пользователь не найден');
        }

        if ($user->verifyTwoFactorCode($request->code)) {
            Auth::login($user);
            session()->forget('2fa_user_id');
            $request->session()->regenerate();
            
            return redirect('/');
        }

        return back()->withErrors([
            'code' => 'Неверный код или срок его действия истёк',
        ]);
    }

    /**
     * Настройки 2FA для авторизованного пользователя
     */
    public function showSettings()
    {
        return view('profile.two-factor-settings');
    }

    /**
     * Включение 2FA с отправкой кода на email
     */
    public function enable(Request $request)
    {
        $user = $request->user();
        
        // Генерируем код
        $code = $user->generateTwoFactorCode();
        
        // Отправляем код на email
        try {
            Mail::to($user->email)->send(
                new TwoFactorCodeMail($code, $user->name, 5)
            );
        } catch (\Exception $e) {
            // В случае ошибки почты, логируем
            \Log::error('Mail error: ' . $e->getMessage());
        }

        return view('profile.two-factor-setup');
    }

    /**
     * Подтверждение включения 2FA
     */
    public function confirmEnable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();
        
        if ($user->verifyTwoFactorCode($request->code)) {
            $user->update([
                'two_factor_enabled' => true,
            ]);
            
            return redirect()->route('profile.edit')
                ->with('success', 'Двухфакторная аутентификация включена');
        }

        return back()->withErrors([
            'code' => 'Неверный код или срок его действия истёк',
        ]);
    }

    /**
     * Отключение 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (!Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
            return back()->withErrors([
                'password' => 'Неверный пароль',
            ]);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        // Удаляем все коды
        $user->twoFactorCodes()->delete();

        return redirect()->route('profile.edit')
            ->with('success', 'Двухфакторная аутентификация отключена');
    }

    /**
     * Повторная отправка 2FA-кода
     */
    public function resend(Request $request)
    {
        if (!session()->has('2fa_user_id')) {
            return response()->json(['error' => 'Сессия не найдена'], 400);
        }

        $user = User::find(session('2fa_user_id'));

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        $code = $user->generateTwoFactorCode();
        
        try {
            Mail::to($user->email)->send(
                new TwoFactorCodeMail($code, $user->name, 5)
            );
        } catch (\Exception $e) {
            \Log::error('Mail error: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка отправки'], 500);
        }

        return response()->json(['success' => true]);
    }
}
