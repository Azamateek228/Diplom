<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Показать форму входа
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Обработка входа с проверкой блокировки и 2FA
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Находим пользователя
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Пользователь не найден',
            ])->withInput($request->only('email'));
        }

        // Проверка блокировки
        if ($user->isLocked()) {
            $remainingMinutes = $user->lock_expires_at 
                ? ceil($user->lock_expires_at->diffInMinutes(now())) 
                : 30;
            
            return back()->withErrors([
                'email' => "Аккаунт заблокирован. Попробуйте через {$remainingMinutes} мин.",
            ])->withInput($request->only('email'));
        }

        // Проверка пароля
        if (!Hash::check($request->password, $user->password)) {
            $user->incrementFailedLoginAttempts();
            
            return back()->withErrors([
                'email' => 'Неверный логин или пароль',
            ])->withInput($request->only('email'));
        }

        // Сброс счётчика неудачных попыток при успешном входе
        $user->resetFailedLoginAttempts();

        // Если включена 2FA, отправляем email-код и завершаем вход только после проверки
        if ($user->two_factor_enabled) {
            $code = $user->generateTwoFactorCode();

            try {
                Mail::to($user->email)->send(new TwoFactorCodeMail($code, $user->name, 5));
            } catch (\Throwable $e) {
                Log::error('Two-factor login mail error', ['user_id' => $user->id, 'message' => $e->getMessage()]);

                return back()->withErrors([
                    'email' => 'Не удалось отправить код подтверждения. Попробуйте позже.',
                ])->withInput($request->only('email'));
            }

            session([
                '2fa_user_id' => $user->id,
                '2fa_user_email' => $user->email,
                '2fa_remember' => $request->filled('remember'),
                '2fa_last_sent_at' => now()->timestamp,
            ]);

            return redirect()->route('two-factor.verify');
        }

        // Обычный вход
        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();
        
        return redirect('/');
    }

    /**
     * Показать форму регистрации
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Обработка регистрации с расширенной валидацией
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                // Минимум 1 заглавная буква
                'regex:/[A-ZА-ЯЁ]/',
                // Минимум 1 строчная буква
                'regex:/[a-zа-яё]/',
                // Минимум 1 цифра
                'regex:/[0-9]/',
                // Минимум 1 специальный символ
                'regex:/[!@#$%^&*(),.?":{}|<>_\-\+=\[\]\\;\'\/`~]/',
            ],
            'password_confirmation' => 'required',
            'accept_terms' => 'accepted',
        ], [
            'name.regex' => 'Имя может содержать только буквы, пробелы и дефис',
            'password.regex' => 'Пароль должен содержать заглавные и строчные буквы, цифры и специальные символы',
            'password.confirmed' => 'Пароли не совпадают',
            'accept_terms.accepted' => 'Необходимо принять условия соглашения',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', 'Регистрация успешна! Войдите.');
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
