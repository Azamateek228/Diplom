<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кинотеатр на колёсах</title>
    @php
        $viteManifestExists = file_exists(public_path('build/manifest.json'));
        $viteDevServerIsRunning = file_exists(public_path('hot'));
    @endphp

    @if ($viteDevServerIsRunning || $viteManifestExists)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @endif
</head>

<body class="app-body">
    <nav class="navbar app-navbar" data-app-nav>
        <div class="container navbar-shell">
            <a class="navbar-brand" href="{{ url('/') }}" aria-label="На главную">
                <span class="brand-mark">🎬</span>
                <span>Кино на колёсах</span>
            </a>

            <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false" aria-controls="main-navigation">
                <span></span>
                <span></span>
                <span></span>
                <span class="visually-hidden">Открыть меню</span>
            </button>

            <div class="navbar-links" id="main-navigation" data-nav-links>
                <a class="nav-pill {{ request()->routeIs('movies.*') ? 'is-active' : '' }}" href="{{ route('movies.index') }}">Фильмы</a>
                <a class="nav-pill {{ request()->routeIs('afisha.*') ? 'is-active' : '' }}" href="{{ route('afisha.index') }}">Афиша</a>
                <a class="nav-pill {{ request()->is('map') ? 'is-active' : '' }}" href="{{ url('/map') }}">Карта</a>
                @auth
                    <a class="nav-pill {{ request()->routeIs('profile.*') || request()->routeIs('tickets.*') ? 'is-active' : '' }}" href="{{ route('profile.edit') }}">Профиль</a>
                    @if(auth()->user()->role === 'admin')
                        <a class="nav-pill nav-pill--accent {{ request()->routeIs('admin.*') || request()->routeIs('cities.*') ? 'is-active' : '' }}" href="{{ route('admin.stats') }}">Админ</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="nav-form">
                        @csrf
                        <button type="submit" class="nav-pill nav-pill--danger">Выйти</button>
                    </form>
                @else
                    <a class="nav-pill nav-pill--accent {{ request()->routeIs('login') ? 'is-active' : '' }}" href="{{ route('login') }}">Войти</a>
                    <a class="nav-pill {{ request()->routeIs('register') ? 'is-active' : '' }}" href="{{ route('register') }}">Регистрация</a>
                @endauth
            </div>
        </div>
    </nav>

    @if (auth()->check() && isset($currentCity) && $currentCity && auth()->user()->city_id === $currentCity->id)
        <div class="cinema-in-your-city-bar text-center py-2">🎉 Сегодня кинотеатр в вашем городе!</div>
    @endif

    @php
        $flashTypes = ['success' => 'Успешно', 'error' => 'Ошибка', 'warning' => 'Внимание', 'info' => 'Информация'];
    @endphp
    <div class="toast-stack" data-toast-stack aria-live="polite" aria-atomic="true">
        @foreach ($flashTypes as $type => $label)
            @if (session($type))
                <div class="app-toast app-toast--{{ $type }}" role="alert" data-toast>
                    <div>
                        <strong>{{ $label }}</strong>
                        <p>{{ session($type) }}</p>
                    </div>
                    <button type="button" class="app-toast-close" aria-label="Закрыть уведомление" data-toast-close>×</button>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div class="app-toast app-toast--error" role="alert" data-toast>
                <div>
                    <strong>Проверьте форму</strong>
                    <p>Исправьте отмеченные поля и повторите отправку.</p>
                </div>
                <button type="button" class="app-toast-close" aria-label="Закрыть уведомление" data-toast-close>×</button>
            </div>
        @endif
    </div>

    <main class="container mt-4">
        @yield('content')
    </main>
</body>

</html>
