<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Кинотеатр на колёсах</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&display=swap" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container d-flex flex-nowrap justify-content-between align-items-center">
            <a class="navbar-brand mb-0 me-3" href="/">Кино на колёсах</a>
            <div class="d-flex align-items-center flex-wrap gap-2 justify-content-end">
                <a class="btn btn-outline-light btn-sm" href="/movies">Фильмы</a>
                <a class="btn btn-outline-light btn-sm" href="{{ route('afisha.index') }}">Афиша</a>
                <a class="btn btn-outline-light btn-sm" href="/map">Где мы?</a>
                @auth
                    <a class="btn btn-outline-light btn-sm" href="{{ route('profile.edit') }}">Профиль</a>
                    @if(auth()->user()->role === 'admin')
                        <a class="btn btn-warning btn-sm" href="{{ route('admin.stats') }}">Админ-панель</a>
                    @endif
                    <form method="POST" action="/logout" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Выйти</button>
                    </form>
                @else
                    <a class="btn btn-success btn-sm" href="/login">Войти</a>
                @endauth
            </div>
        </div>
    </nav>

    @if (auth()->check() && isset($currentCity) && $currentCity && auth()->user()->city_id === $currentCity->id)
        <div class="cinema-in-your-city-bar text-center py-2">
            🎉 Сегодня кинотеатр в вашем городе!
        </div>
    @endif

    <div class="container mt-4">
        @yield('content')
    </div>

</body>

</html>
