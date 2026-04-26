@extends('layouts.app')

@section('content')
    <div class="afisha-page">
        <h2 class="mb-4">Афиша на неделю</h2>

        @if (empty($weeklySchedule))
            <div class="empty-state">
                <div class="empty-state-icon">🗺️</div>
                <h4>Маршрут пока не сформирован</h4>
                <p>Добавьте города с координатами и фильмы, чтобы построить тур на неделю.</p>
                <a href="{{ route('movies.index') }}" class="btn btn-main btn-sm">Перейти к фильмам</a>
            </div>
        @else
            <div class="alert alert-info mb-4">
                Маршрут формируется автоматически по ближайшим городам, а фильм в городе выбирается по результатам голосования.
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Порядок маршрута</h5>
                    <div class="route-timeline">
                        @foreach ($routeCities as $idx => $city)
                            <div class="route-step">
                                <span class="route-step-index">{{ $idx + 1 }}</span>
                                <span class="route-step-name">{{ $city->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row g-3">
                @foreach ($weeklySchedule as $day)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 afisha-card fade-in-up">
                            <div class="card-body">
                                @php
                                    $availableTickets = max(0, $day['capacity'] - $day['sold']);
                                    $fillPercentage = $day['capacity'] > 0 ? min(100, (int) round(($day['sold'] / $day['capacity']) * 100)) : 0;
                                    $availabilityLabel = 'Билеты есть';
                                    $availabilityClass = 'bg-success';
                                    if ($availableTickets === 0) {
                                        $availabilityLabel = 'Билеты закончились';
                                        $availabilityClass = 'bg-danger';
                                    } elseif ($availableTickets <= 10) {
                                        $availabilityLabel = 'Осталось мало';
                                        $availabilityClass = 'bg-warning text-dark';
                                    }
                                @endphp
                                <div class="text-muted small mb-2">{{ $day['date'] }}</div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h5 class="card-title mb-0">{{ $day['city']->name }}</h5>
                                    <span class="badge {{ $availabilityClass }}">{{ $availabilityLabel }}</span>
                                </div>
                                @if (!empty($day['movie']) && $day['movie']->poster)
                                    <img class="afisha-poster mb-2" src="{{ asset($day['movie']->poster) }}" alt="{{ $day['movie']->title }}">
                                @endif
                                <p class="mb-1"><strong>Фильм:</strong> {{ $day['movie']->title ?? 'Пока не назначен' }}</p>
                                <p class="mb-1"><strong>Время:</strong> {{ $day['show_time'] }}</p>
                                <p class="mb-1"><strong>Площадка:</strong> {{ $day['movie']->venue ?? 'Уточняется' }}</p>
                                <p class="mb-1"><strong>Расписание:</strong> {{ $day['date'] }} / {{ $day['city']->name }} / {{ $day['movie']->title ?? '—' }}</p>
                                <p class="mb-1"><strong>Цена билета:</strong> {{ $ticketPrice }} ₽</p>
                                <p class="mb-0"><strong>Возраст:</strong>
                                    @if (!empty($day['movie']) && $day['movie']->age_rating !== null)
                                        {{ $day['movie']->age_rating }}+
                                    @else
                                        —
                                    @endif
                                </p>
                                <div class="movie-session-kpi mt-3">
                                    <div class="session-meta">
                                        <span>Продано: <strong>{{ $day['sold'] }}</strong></span>
                                        <span>Свободно: <strong>{{ $availableTickets }}</strong></span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: {{ $fillPercentage }}%"></div>
                                    </div>
                                    <small class="d-block mt-2 text-muted">Заполняемость: {{ $fillPercentage }}%</small>
                                </div>
                                @if (!empty($day['movie']))
                                    <div class="mt-3 d-flex gap-2 flex-wrap">
                                        <a href="{{ route('movies.show', $day['movie']) }}" class="btn btn-outline-secondary btn-sm">Подробнее</a>
                                        @auth
                                            @if ($availableTickets > 0)
                                                <a href="{{ route('tickets.create', ['city_id' => $day['city']->id, 'movie_id' => $day['movie']->id, 'show_date' => $day['show_date'], 'show_time' => $day['show_time']]) }}" class="btn btn-primary btn-sm">Купить билет</a>
                                            @else
                                                <span class="badge bg-danger">Билеты закончились</span>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Войти для покупки</a>
                                        @endauth
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
