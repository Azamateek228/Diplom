<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Показать форму запроса сброса пароля
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Обработка запроса сброса пароля с отправкой email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Удаляем старые токены
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Генерируем токен
        $token = Str::random(60);

        // Сохраняем токен в БД
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Получаем пользователя
        $user = User::where('email', $request->email)->first();
        
        // Отправляем email
        try {
            Mail::to($user->email)->send(
                new ResetPasswordMail($token, $user->name, $user->email, 24)
            );
        } catch (\Exception $e) {
            \Log::error('Mail error: ' . $e->getMessage());
            // В продакшене здесь можно отправить алерт админу
        }

        return redirect()->route('login')
            ->with('success', 'Ссылка для сброса пароля отправлена на вашу почту. Проверьте папку "Спам", если письмо не пришло.');
    }

    /**
     * Показать форму сброса пароля
     */
    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Обработка сброса пароля
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'regex:/[A-ZА-ЯЁ]/',
                'regex:/[a-zа-яё]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>_\-\+=\[\]\\;\'\/`~]/',
            ],
            'password_confirmation' => 'required',
        ], [
            'password.regex' => 'Пароль должен содержать заглавные и строчные буквы, цифры и специальные символы',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        // Проверяем токен
        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetToken || !Hash::check($request->token, $resetToken->token)) {
            return back()->withErrors([
                'token' => 'Неверный токен сброса пароля',
            ]);
        }

        // Проверяем срок действия токена (24 часа)
        if (now()->diffInHours($resetToken->created_at) > 24) {
            return back()->withErrors([
                'token' => 'Срок действия токена истёк',
            ]);
        }

        // Сбрасываем пароль
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Удаляем использованный токен
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Пароль успешно изменён! Войдите.');
    }
}
