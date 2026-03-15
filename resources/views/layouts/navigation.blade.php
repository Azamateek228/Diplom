<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top" aria-label="Основная навигация">
    <div class="container d-flex flex-nowrap justify-content-between align-items-center">
        <a class="navbar-brand mb-0 me-3 fw-bold" href="/" title="Кинотеатр на колёсах — главная страница">
            🎬 Кино на колёсах
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" 
                aria-controls="mainNav" aria-expanded="false" aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse justify-content-end" id="mainNav">
            <ul class="navbar-nav align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('movies.index') }}" title="Афиша фильмов">🎬 Афиша</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('route.index') }}" title="Расписание показов">🗓️ Маршрут</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/map" title="Карта маршрута кинотеатра">🗺️ Где мы?</a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.edit') }}" title="Личный кабинет">👤 Профиль</a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link btn btn-warning btn-sm text-dark" href="{{ route('admin.stats') }}" title="Панель администратора">⚙️ Админ-панель</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-warning btn-sm text-dark" href="{{ route('route.admin') }}" title="Управление маршрутами">🚌 Маршруты</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <form method="POST" action="/logout" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" title="Выйти из аккаунта">Выйти</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link btn btn-success btn-sm" href="/login" title="Войти в личный кабинет">🔐 Войти</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@if (auth()->check() && isset($currentCity) && $currentCity && auth()->user()->city_id === $currentCity->id)
    <div class="cinema-in-your-city-bar text-center py-2" role="alert" aria-live="polite">
        🎉 Сегодня кинотеатр в вашем городе <strong>{{ $currentCity->name }}</strong>!
    </div>
@endif
