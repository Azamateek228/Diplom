<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\SitemapController;

// Маршруты аутентификации
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Восстановление пароля
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])
    ->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
    ->name('password.reset.form');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->name('password.update');

// Двухфакторная аутентификация
Route::get('/two-factor/verify', [TwoFactorController::class, 'showVerify'])
    ->name('two-factor.verify');
Route::post('/two-factor/verify', [TwoFactorController::class, 'verify']);
Route::post('/two-factor/resend', [TwoFactorController::class, 'resend'])
    ->name('two-factor.resend');

// Маршруты для фильмов
Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/create', [MovieController::class, 'create'])->middleware('admin')->name('movies.create');
Route::post('/movies', [MovieController::class, 'store'])->middleware('admin')->name('movies.store');
Route::get('/movies/{movie}/edit', [MovieController::class, 'edit'])->middleware('admin')->name('movies.edit');
Route::put('/movies/{movie}', [MovieController::class, 'update'])->middleware('admin')->name('movies.update');
Route::delete('/movies/{movie}', [MovieController::class, 'destroy'])->middleware('admin')->name('movies.destroy');

// Маршруты для городов
Route::get('/cities', [CityController::class, 'index'])->middleware('admin')->name('cities.index');
Route::get('/cities/create', [CityController::class, 'create'])->middleware('admin')->name('cities.create');
Route::post('/cities', [CityController::class, 'store'])->middleware('admin')->name('cities.store');
Route::get('/cities/{city}/edit', [CityController::class, 'edit'])->middleware('admin')->name('cities.edit');
Route::put('/cities/{city}', [CityController::class, 'update'])->middleware('admin')->name('cities.update');
Route::delete('/cities/{city}', [CityController::class, 'destroy'])->middleware('admin')->name('cities.destroy');

// Главная страница
Route::get('/', function () {
    return view('home');
})->name('home');

// Sitemap.xml для SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Карта
Route::get('/map', [MapController::class, 'index']);

// Маршрут на неделю
Route::get('/route', [RouteController::class, 'index'])->name('route.index');
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/route/admin', [RouteController::class, 'admin'])->name('route.admin');
    Route::post('/route', [RouteController::class, 'store'])->name('route.store');
    Route::put('/route/{route}', [RouteController::class, 'update'])->name('route.update');
    Route::delete('/route/{route}', [RouteController::class, 'destroy'])->name('route.destroy');
});

// Защищённые маршруты (требуется аутентификация)
Route::middleware('auth')->group(function () {
    // Голосования
    Route::get('/votes', [VoteController::class, 'index'])->name('votes.index');
    Route::post('/votes', [VoteController::class, 'store'])->name('votes.store');
    
    // Профиль
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Двухфакторная аутентификация (настройки)
    Route::get('/two-factor/settings', [TwoFactorController::class, 'showSettings'])
        ->name('two-factor.settings');
    Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])
        ->name('two-factor.enable');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirmEnable'])
        ->name('two-factor.confirm');
    Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])
        ->name('two-factor.disable');
});

// Админ-панель
Route::middleware(['auth'])->group(function () {
    Route::match(['get', 'post'], '/admin/stats', [AdminController::class, 'stats'])
        ->name('admin.stats');
});

// Ресурсный маршрут для фильмов (дублирование, можно удалить)
Route::resource('movies', MovieController::class);
