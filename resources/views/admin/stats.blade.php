@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="admin-page">
    <div class="admin-header">
        <h2 class="mb-4">Панель администратора</h2>
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
                <h5>Пользователи</h5>
                <h3>{{ $usersCount }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Голосов</h5>
                <h3>{{ $votesCount }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Куплено билетов</h5>
                <h3>{{ $ticketsPurchased }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Загрузка мест</h5>
                <h3>{{ $overallLoadPercent }}%</h3>
                <div class="kpi-progress mt-2">
                    <div class="kpi-progress-bar" style="width: {{ $overallLoadPercent }}%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Текущий город тура</h5>
                <p>{{ $currentCityName ?? 'Не выбран' }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Топ-фильм</h5>
                <p>{{ $topMovie?->title ?? '—' }}</p>
                @if($topMovie)
                    <small>Голосов: {{ $topMovie->votes_count }}</small>
                @endif
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <h5>Топ-город</h5>
                <p>{{ $topCity?->name ?? '—' }}</p>
                @if($topCity)
                    <small>Голосов: {{ $topCity->votes_count }}</small>
                @endif
            </div>
        </div>
    </div>

    <!-- Текущий город кинотеатра -->
    <div class="admin-section mb-5">
        <h3 class="section-title">Текущий город кинотеатра</h3>
        <p class="text-muted mb-2">
            @if($currentCityId)
                Сейчас: <strong>{{ $cities->find($currentCityId)?->name ?? '—' }}</strong>
            @else
                Не выбран
            @endif
        </p>
        <form method="POST" action="{{ route('admin.stats') }}" class="d-flex flex-wrap gap-2 align-items-end">
            @csrf
            <div class="mb-0">
                <label class="form-label small">Выбрать город</label>
                <select name="current_city_id" class="form-select" style="width: auto;">
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
        </form>
    </div>

    <!-- Фильтр по городу -->
    <div class="admin-section mb-5">
        <h3 class="section-title">Фильтр по городу</h3>
        <form method="GET" action="{{ route('admin.stats') }}" class="city-filter-form">
            <select name="city_id" class="form-select" onchange="this.form.submit()">
                <option value="">Все города</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ $selectedCityId == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Фильмы по городам -->
    <div class="admin-section mb-5">
        <h3 class="section-title">
            Фильмы 
            @if($selectedCityId)
                в городе: {{ $cities->find($selectedCityId)?->name }}
            @else
                (все города)
            @endif
        </h3>
        
        @if($movies->isEmpty())
            <div class="no-data">
                <p>Фильмы не найдены</p>
            </div>
        @else
            <div class="movies-admin-grid">
                @foreach($movies as $movie)
                    <div class="movie-admin-card {{ $movie->show_time && \Illuminate\Support\Carbon::parse($movie->show_time)->isPast() ? 'is-past' : 'is-upcoming' }}">
                        @if($movie->poster)
                            <div class="movie-poster-preview">
                                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
                            </div>
                        @endif
                        
                        <div class="movie-admin-header">
                            <h4>{{ $movie->title }}</h4>
                            @if($movie->city)
                                <span class="city-badge">{{ $movie->city->name }}</span>
                            @endif
                        </div>
                        
                        <div class="movie-admin-details">
                            @if($movie->genre)
                                <div class="detail-row">
                                    <span class="label">Жанр:</span>
                                    <span class="value">{{ $movie->genre }}</span>
                                </div>
                            @endif
                            
                            @if($movie->age_rating)
                                <div class="detail-row">
                                    <span class="label">Возраст:</span>
                                    <span class="value">{{ $movie->age_rating }}+</span>
                                </div>
                            @endif
                            
                            @if($movie->duration)
                                <div class="detail-row">
                                    <span class="label">Длительность:</span>
                                    <span class="value">{{ $movie->duration }} мин</span>
                                </div>
                            @endif
                            
                            @if($movie->venue)
                                <div class="detail-row">
                                    <span class="label">Площадка:</span>
                                    <span class="value">{{ $movie->venue }}</span>
                                </div>
                            @endif

                            @if($movie->show_time)
                                <div class="detail-row">
                                    <span class="label">Время:</span>
                                    <span class="value">{{ \Illuminate\Support\Carbon::parse($movie->show_time)->format('d.m.Y H:i') }}</span>
                                </div>
                            @endif

                            @if($movie->venue_capacity)
                                <div class="detail-row">
                                    <span class="label">Вместимость:</span>
                                    <span class="value">{{ $movie->venue_capacity }} чел.</span>
                                </div>
                            @endif
                            
                            @if($movie->expected_attendees)
                                <div class="detail-row highlight">
                                    <span class="label">👥 Ожидается зрителей:</span>
                                    <span class="value">{{ $movie->expected_attendees }}</span>
                                </div>
                            @endif
                            
                            <div class="detail-row">
                                <span class="label"> Голосов:</span>
                                <span class="value">{{ $movie->votes_count ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="movie-admin-actions">
                            <form method="POST" action="{{ route('movies.update-show-time', $movie) }}" class="quick-showtime-form">
                                @csrf
                                @method('PATCH')
                                <input type="datetime-local" name="show_time" class="form-control form-control-sm"
                                    value="{{ $movie->show_time ? \Illuminate\Support\Carbon::parse($movie->show_time)->format('Y-m-d\TH:i') : now()->addDay()->format('Y-m-d\TH:i') }}" required>
                                <button type="submit" class="btn btn-sm btn-warning">Обновить дату</button>
                            </form>
                            <form method="POST" action="{{ route('movies.duplicate', $movie) }}" class="duplicate-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-info">Дублировать</button>
                            </form>
                            <a href="{{ route('movies.edit', $movie) }}" class="btn-edit">
                                 Редактировать
                            </a>
                            <form method="POST" action="{{ route('movies.destroy', $movie) }}" class="delete-form" onsubmit="return confirm('Вы уверены, что хотите удалить этот фильм?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                     Удалить
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Статистика по городам -->
    <div class="admin-section">
        <h3 class="section-title"> Статистика по городам</h3>
        @if($cityStats->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🏙️</div>
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
                                <span class="stat-label"> Ожидается зрителей:</span>
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
