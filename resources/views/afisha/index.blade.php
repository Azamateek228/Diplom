@extends('layouts.app')

@section('content')
    <div class="afisha-page">
        <section class="afisha-hero page-hero fade-in-up">
            <span class="hero-kicker">Передвижной кинотеатр</span>
            <h2>Афиша тура</h2>
            <p>Расписание ближайших выездных кинопоказов по городам маршрута</p>
        </section>

        @if (empty($weeklySchedule))
            <div class="empty-state">
                <h4>Маршрут пока не сформирован</h4>
                <p>Добавьте города с координатами и фильмы, чтобы построить тур на неделю.</p>
                <a href="{{ route('movies.index') }}" class="btn btn-main btn-sm">Перейти к фильмам</a>
            </div>
        @else
            <section class="route-panel fade-in-up">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
                    <div>
                        <span class="section-kicker">Маршрут недели</span>
                        <h5 class="mb-1">Логичная последовательность остановок</h5>
                    </div>
                    <span class="route-hint">{{ $routeLabel ?? 'Фильм выбирается по голосованию в каждом городе' }}</span>
                </div>

                <div class="route-timeline">
                    @foreach ($routeCities as $idx => $city)
                        <div class="route-step">
                            <span class="route-step-index">{{ $idx + 1 }}</span>
                            <span class="route-step-name">{{ $city->name }}@if(($city->votes_count ?? 0) > 0) — {{ $city->votes_count }} голос(ов)@endif</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="schedule-grid">
                @foreach ($weeklySchedule as $dayIndex => $day)
                    @php
                        $availableTickets = max(0, $day['capacity'] - $day['sold']);
                        $fillPercentage = $day['capacity'] > 0 ? min(100, (int) round(($day['sold'] / $day['capacity']) * 100)) : 0;
                        $availabilityLabel = 'Билеты есть';
                        $availabilityClass = 'ticket-availability--available';
                        if ($availableTickets === 0) {
                            $availabilityLabel = 'Билеты закончились';
                            $availabilityClass = 'ticket-availability--sold-out';
                        } elseif ($availableTickets <= 10) {
                            $availabilityLabel = 'Осталось мало';
                            $availabilityClass = 'ticket-availability--limited';
                        }
                    @endphp

                    <article class="schedule-card fade-in-up">
                        <div class="schedule-card-topline">
                            <div>
                                <span class="schedule-date">День {{ $dayIndex + 1 }}</span>
                                <p class="schedule-date-value">{{ $day['date'] }}</p>
                            </div>
                            <span class="ticket-availability {{ $availabilityClass }}">{{ $availabilityLabel }}</span>
                        </div>

                        <div class="schedule-card-body">
                            <div class="schedule-poster-wrap">
                                @if (!empty($day['movie']) && $day['movie']->poster)
                                    <img class="schedule-poster" src="{{ $day['movie']->poster_url }}" alt="{{ $day['movie']->title }}">
                                @elseif (!empty($day['movie']))
                                    <div class="schedule-poster-fallback">
                                        <strong>{{ $day['movie']->title }}</strong>
                                    </div>
                                @else
                                    <div class="schedule-poster-fallback schedule-poster-fallback--empty">
                                        <strong>Фильм скоро появится</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="schedule-info">
                                <h3 class="schedule-city">{{ $day['city']->name }}</h3>

                                @if (!empty($day['movie']))
                                    <h4 class="schedule-movie">{{ $day['movie']->title }}</h4>
                                    <div class="schedule-meta">
                                        <span>Время: {{ $day['show_time'] }}–{{ $day['end_time'] }}</span>
                                        <span>Площадка: {{ $day['venue'] }}</span>
                                        <span>Цена билета: {{ $ticketPrice }} ₽</span>
                                        <span>Свободно мест: {{ $availableTickets }} из {{ $day['capacity'] }}</span>
                                    </div>
                                @else
                                    <div class="schedule-movie schedule-movie--empty">Фильм пока не назначен</div>
                                    <div class="schedule-meta">
                                        <span>Время: {{ $day['show_time'] }}–{{ $day['end_time'] }}</span>
                                        <span>Площадка: {{ $day['venue'] }}</span>
                                        <span>Цена билета: {{ $ticketPrice }} ₽</span>
                                        <span>Свободно мест: {{ $availableTickets }} из {{ $day['capacity'] }}</span>
                                    </div>
                                @endif

                                <div class="movie-session-kpi mt-3">
                                    <div class="session-meta">
                                        <span>Продано билетов: <strong>{{ $day['sold'] }}</strong></span>
                                        <span>Свободно мест: <strong>{{ $availableTickets }}</strong></span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: {{ $fillPercentage }}%"></div>
                                    </div>
                                    <small class="d-block mt-2 text-muted">Заполненность: {{ $fillPercentage }}%</small>
                                </div>

                                @if (!empty($day['movie']))
                                    <div class="schedule-actions">
                                        <a href="{{ route('movies.show', $day['movie']) }}" class="btn btn-outline-secondary btn-sm">Подробнее</a>
                                        @auth
                                            @if ($availableTickets > 0)
                                                <a href="{{ route('tickets.create', ['city_id' => $day['city']->id, 'movie_id' => $day['movie']->id, 'show_date' => $day['show_date'], 'show_time' => $day['show_time']]) }}" class="btn btn-primary btn-sm">Купить билет</a>
                                            @else
                                                <span class="ticket-availability ticket-availability--sold-out">Билеты закончились</span>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Войти для покупки</a>
                                        @endauth
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </div>
@endsection
