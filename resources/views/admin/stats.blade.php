@extends('layouts.app')

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <div><span class="eyebrow">Управление маршрутом и показами</span><h2 class="mb-2">Панель администратора</h2><p class="text-muted">Здесь видно спрос по городам, продажи билетов, текущую точку кинофургона и параметры голосования.</p></div>
        <div class="admin-header-actions">
            <a href="{{ route('movies.create') }}" class="btn btn-success">
                Добавить фильм
            </a>
            <a href="{{ route('cities.index') }}" class="btn btn-info">
                Управление городами
            </a>
        </div>
    </div>

    <!-- Общая статистика -->
    <div class="stats-grid mb-5">
        <div class="stat-card">
            <div class="stat-content">
                <h5>Зрители в системе</h5><small>все зарегистрированные аккаунты</small>
                <h3>{{ $usersCount }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Голоса за фильмы</h5><small>учтённый спрос выбранных городов</small>
                <h3>{{ $votesCount }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Проданные билеты</h5><small>только активные оплаченные места</small>
                <h3>{{ $ticketsPurchased }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Средняя загрузка</h5><small>продажи относительно вместимости площадок</small>
                <h3>{{ $overallLoadPercent }}%</h3>
                <div class="kpi-progress mt-2">
                    <div class="kpi-progress-bar" style="width: {{ $overallLoadPercent }}%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Текущий город тура</h5><small>куда едет кинофургон</small>
                <p>{{ $currentCityName ?? 'Не выбран' }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Топ-фильм</h5><small>лидер голосования</small>
                <p>{{ $topMovie?->title ?? '—' }}</p>
                @if($topMovie)
                    <small>Голосов: {{ $topMovie->votes_count }}</small>
                @endif
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Топ-город</h5><small>самый активный город</small>
                <p>{{ $topCity?->name ?? '—' }}</p>
                @if($topCity)
                    <small>Голосов: {{ $topCity->votes_count }}</small>
                @endif
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Тип маршрута</h5><small>зависит от наличия голосов</small>
                <p>{{ ($routeType ?? 'long') === 'short' ? 'короткий по голосам' : 'длинный' }}</p>
            </div>
        </div>
    </div>


    <div class="admin-section mb-5">
        <h3 class="section-title">Маршрут по голосам</h3>
        <p class="section-note">{{ $routeLabel }}</p>
        <div class="route-timeline">
            @foreach($routeCities as $idx => $city)
                <div class="route-step">
                    <span class="route-step-index">{{ $idx + 1 }}</span>
                    <span class="route-step-name">{{ $city->name }}@if(($city->votes_count ?? 0) > 0) — {{ $city->votes_count }} голос(ов)@endif</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="admin-section mb-5">
        <h3 class="section-title">Фильмы-победители по городам маршрута</h3>
        <div class="city-stats-grid">
            @foreach($cityWinners as $winner)
                <div class="city-stat-card">
                    <h4>{{ $winner['city']->name }}</h4>
                    <p>{{ $winner['movie']?->title ?? 'Фильм будет выбран после голосования' }}</p>
                    <small>Голосов за победителя: {{ $winner['votes_count'] }}</small>
                </div>
            @endforeach
        </div>
    </div>


    <div class="admin-dashboard-grid mb-5">
        <section class="admin-section quick-actions-card">
            <h3 class="section-title">Быстрые действия</h3>
            <div class="quick-actions-grid">
                <a href="{{ route('movies.create') }}" class="quick-action">Добавить фильм</a>
                <a href="{{ route('cities.create') }}" class="quick-action">Добавить город</a>
                <a href="{{ route('cities.index') }}" class="quick-action">Управлять маршрутом</a>
                <a href="{{ route('movies.index') }}" class="quick-action">Открыть витрину</a>
            </div>
        </section>

        <section class="admin-section upcoming-sessions-card">
            <h3 class="section-title">Ближайшие сеансы</h3>
            @if($upcomingSessions->isEmpty())
                <div class="empty-state compact-empty">
                    <h4>Сеансов пока нет</h4>
                    <p>Сеансы теперь формируются в афише по городам маршрута и фильмам-победителям голосования.</p>
                </div>
            @else
                <div class="upcoming-sessions-list">
                    @foreach($upcomingSessions as $session)
                        <a href="{{ route('movies.edit', $session) }}" class="upcoming-session-item">
                            <strong>{{ $session->title }}</strong>
                            <span>{{ $session->city?->name ?? 'Город уточняется' }} • {{ $session->show_time?->format('d.m.Y H:i') }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <!-- Текущий город кинотеатра -->
    <div class="admin-section mb-5">
        <h3 class="section-title">Текущий город кинотеатра</h3><p class="section-note">Администратор может вручную выбрать город, обновить дедлайн голосования и цену билета для всех будущих сеансов.</p>
        <p class="text-muted mb-2">
            @if($currentCityId)
                Сейчас: <strong>{{ $cities->firstWhere('id', $currentCityId)?->name ?? '—' }}</strong>
            @else
                Не выбран
            @endif
        </p>
        <form method="POST" action="{{ route('admin.stats') }}" class="admin-settings-form">
            @csrf
            <div class="mb-0">
                <label class="form-label small">Выбрать город</label>
                <select name="current_city_id" class="form-select">
                    <option value="">— сбросить —</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ $currentCityId == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-0">
                <label class="form-label small">Дедлайн голосования</label>
                <input type="datetime-local" name="voting_deadline" class="form-control"
                    value="{{ $votingDeadline ? \Illuminate\Support\Carbon::parse($votingDeadline)->format('Y-m-d\TH:i') : '' }}">
            </div>
            <div class="mb-0">
                <label class="form-label small">Цена билета (₽)</label>
                <input type="number" name="ticket_price" class="form-control" min="100" max="5000" value="{{ $ticketPrice }}">
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <button type="submit" name="auto_city" value="1" class="btn btn-warning">Выбрать по спросу</button>
        </form>
    </div>

    <!-- Фильмы каталога -->
    <div class="admin-section mb-5 admin-movies-section">
        <div class="admin-section-heading">
            <div>
                <h3 class="section-title">
                    Фильмы каталога @if($selectedCityId) с голосами в городе: {{ $cities->firstWhere('id', $selectedCityId)?->name }} @else (все фильмы) @endif
                </h3>
                <p class="section-note">Поиск, статус показа и город сохраняются при пагинации.</p>
            </div>
            @if($movies->total() > 0)
                <span class="result-counter">Показано {{ $movies->firstItem() }}–{{ $movies->lastItem() }} из {{ $movies->total() }}</span>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.stats') }}" class="admin-movie-filter-panel">
            <label class="filter-field">
                <span>Поиск фильма</span>
                <input type="search" name="admin_movie_search" class="form-control" value="{{ $adminMovieFilters['search'] ?? '' }}" placeholder="Название фильма">
            </label>
            <label class="filter-field">
                <span>Город по голосам</span>
                <select name="city_id" class="form-select">
                    <option value="">Все города</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ $selectedCityId == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="filter-field">
                <span>Статус</span>
                <select name="admin_movie_status" class="form-select">
                    <option value="all" {{ ($adminMovieFilters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Все статусы</option>
                    <option value="actual" {{ ($adminMovieFilters['status'] ?? 'all') === 'actual' ? 'selected' : '' }}>Актуальные</option>
                    <option value="past" {{ ($adminMovieFilters['status'] ?? 'all') === 'past' ? 'selected' : '' }}>Прошедшие</option>
                    <option value="no_date" {{ ($adminMovieFilters['status'] ?? 'all') === 'no_date' ? 'selected' : '' }}>Без даты</option>
                </select>
            </label>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Применить</button>
                <a href="{{ route('admin.stats') }}" class="btn btn-secondary">Сбросить</a>
            </div>
        </form>

        @if($movies->isEmpty())
            <div class="no-data">
                <p>Фильмы не найдены</p>
            </div>
        @else
            <div class="movies-admin-grid">
                @foreach($movies as $movie)
                    <article class="movie-admin-card {{ $movie->session_status_class }}">
                        <div class="movie-admin-media">
                            @if($movie->poster)
                                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
                            @else
                                <div class="movie-admin-poster-fallback"><span>🎬</span><strong>Афиша скоро появится</strong></div>
                            @endif
                        </div>

                        <div class="movie-admin-body">
                            <div class="movie-admin-header">
                                <div class="movie-admin-title-wrap">
                                    <h4 title="{{ $movie->title }}">{{ $movie->title }}</h4>
                                    <span class="movie-status-badge {{ $movie->session_status_class }}">{{ $movie->session_status_label }}</span>
                                </div>
                            </div>

                            <div class="movie-admin-details">
                                <div class="detail-row"><span class="label">Жанр</span><span class="value">{{ $movie->genre ?: 'Не указан' }}</span></div>
                                <div class="detail-row"><span class="label">Возраст</span><span class="value">{{ $movie->age_rating ? $movie->age_rating . '+' : 'Не указан' }}</span></div>
                                <div class="detail-row"><span class="label">Длительность</span><span class="value">{{ $movie->duration ? $movie->duration . ' мин' : 'Не указана' }}</span></div>
                                <div class="detail-row"><span class="label">Дата</span><span class="value">{{ $movie->show_time ? \Illuminate\Support\Carbon::parse($movie->show_time)->format('d.m.Y H:i') : 'Без даты показа' }}</span></div>
                                <div class="detail-row"><span class="label">Площадка</span><span class="value">{{ $movie->venue ?: 'Не указана' }}</span></div>
                                <div class="detail-row"><span class="label">Вместимость</span><span class="value">{{ $movie->venue_capacity ? $movie->venue_capacity . ' чел.' : 'Не указана' }}</span></div>
                                <div class="detail-row"><span class="label">Ожидается</span><span class="value">{{ $movie->expected_attendees ?: 'Нет прогноза' }}</span></div>
                                <div class="detail-row highlight"><span class="label">Голоса</span><span class="value">{{ $movie->votes_count ?? 0 }}</span></div>
                            </div>

                            <div class="movie-admin-actions">
                                <form method="POST" action="{{ route('movies.update-show-time', $movie) }}" class="showtime-admin-panel">
                                    @csrf
                                    @method('PATCH')
                                    <label class="showtime-field">
                                        <span>Дата и время показа</span>
                                        <input type="datetime-local" name="show_time" class="form-control"
                                            value="{{ $movie->show_time ? \Illuminate\Support\Carbon::parse($movie->show_time)->format('Y-m-d\TH:i') : now()->addDay()->format('Y-m-d\TH:i') }}" required>
                                    </label>
                                    <button type="submit" class="btn btn-warning btn-admin-action">Обновить дату</button>
                                </form>

                                <div class="movie-admin-action-list">
                                    <form method="POST" action="{{ route('movies.duplicate', $movie) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-admin-action">Дублировать</button>
                                    </form>
                                    <a href="{{ route('movies.edit', $movie) }}" class="btn btn-primary btn-admin-action">Редактировать</a>
                                    <form method="POST" action="{{ route('movies.destroy', $movie) }}" onsubmit="return confirm('Вы уверены, что хотите удалить этот фильм?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-admin-action">Удалить</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="cinema-pagination admin-pagination">
                <div class="pagination-summary">Показано {{ $movies->firstItem() }}–{{ $movies->lastItem() }} из {{ $movies->total() }} фильмов</div>
                <div class="pagination-links">
                    @if ($movies->onFirstPage())
                        <span class="page-link is-disabled">← Назад</span>
                    @else
                        <a class="page-link" href="{{ $movies->previousPageUrl() }}">← Назад</a>
                    @endif

                    @foreach ($movies->getUrlRange(1, $movies->lastPage()) as $page => $url)
                        @if ($page === $movies->currentPage())
                            <span class="page-link is-active">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($movies->hasMorePages())
                        <a class="page-link" href="{{ $movies->nextPageUrl() }}">Вперёд →</a>
                    @else
                        <span class="page-link is-disabled">Вперёд →</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Статистика по городам -->
    <div class="admin-section">
        <h3 class="section-title">Статистика по городам</h3>
        @if($cityStats->isEmpty())
            <div class="empty-state">
                <h4>Нет данных по городам</h4>
                <p>Добавьте города и голоса, чтобы увидеть аналитику тура.</p>
            </div>
        @else
            <div class="cities-stats-grid">
                @foreach($cityStats as $stat)
                    <div class="city-stat-card">
                        <h4>{{ $stat['city']->name }}</h4>
                        <div class="city-stats-details">
                            <div class="stat-item">
                                <span class="stat-label">Фильмов:</span>
                                <span class="stat-value">{{ $stat['movies_count'] }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Голосов:</span>
                                <span class="stat-value">{{ $stat['total_votes'] }}</span>
                            </div>
                            <div class="stat-item {{ $stat['total_expected'] > 0 ? 'highlight' : '' }}">
                                <span class="stat-label">Ожидается зрителей:</span>
                                <span class="stat-value">{{ $stat['total_expected'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
