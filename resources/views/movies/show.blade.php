@extends('layouts.app')

@section('content')
    <div class="movie-show-page">
        <a href="{{ route('movies.index') }}" class="btn btn-outline-dark btn-sm mb-3">← Назад к фильмам</a>

        <article class="movie-show-card">
            <div class="movie-show-poster">
                @if($movie->poster)
                    <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
                @else
                    <div class="poster-fallback poster-fallback--large"><span>🎬</span><strong>{{ $movie->title }}</strong><small>Постер готовится</small></div>
                @endif
            </div>

            <div class="movie-show-info">
                <span class="eyebrow">Подробности сеанса</span>
                <h1>{{ $movie->title }}</h1>
                <p class="lead-text">{{ $movie->description ?? 'Описание фильма появится позже.' }}</p>

                <div class="show-meta-grid">
                    <div><span>Жанр</span><strong>{{ $movie->genre ?? 'Уточняется' }}</strong></div>
                    <div><span>Возраст</span><strong>{{ $movie->age_rating }}+</strong></div>
                    <div><span>Длительность</span><strong>{{ $movie->duration }} мин</strong></div>
                    <div><span>Город</span><strong>{{ $movie->city?->name ?? 'Уточняется' }}</strong></div>
                    <div><span>Площадка</span><strong>{{ $movie->venue ?? 'Уточняется' }}</strong></div>
                    <div><span>Дата показа</span><strong>{{ $movie->show_time?->format('d.m.Y H:i') ?? 'Уточняется' }}</strong></div>
                    <div><span>Вместимость</span><strong>{{ $movie->venue_capacity ?? '—' }} мест</strong></div>
                    <div><span>Голоса</span><strong>{{ $movie->votes_count ?? 0 }}</strong></div>
                </div>

                <div class="movie-session-kpi show-kpi">
                    <div class="session-meta">
                        <span>Продано билетов: <strong>{{ $soldTickets }}</strong></span>
                        <span>Заполняемость: <strong>{{ $fillPercentage }}%</strong></span>
                        <span>Цена: <strong>{{ $ticketPrice }} ₽</strong></span>
                    </div>
                    <div class="progress-bar-container"><div class="progress-bar" style="width: {{ $fillPercentage }}%"></div></div>
                </div>

                <div class="show-actions">
                    @auth
                        <form method="POST" action="{{ route('votes.store') }}" class="vote-form vote-form-inline">
                            @csrf
                            <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                            <input type="hidden" name="city_id" value="{{ $movie->city_id }}">
                            <input type="number" name="expected_attendees" class="form-control" min="1" max="10" value="1">
                            <button class="vote-btn" {{ $votingClosed ? 'disabled' : '' }}>{{ $votingClosed ? 'Голосование закрыто' : 'Голосовать за фильм' }}</button>
                        </form>

                        @if(Route::has('tickets.create') && $movie->city_id && $movie->show_time)
                            <a href="{{ route('tickets.create', ['city_id' => $movie->city_id, 'movie_id' => $movie->id, 'show_date' => $movie->show_time->toDateString(), 'show_time' => $movie->show_time->format('H:i')]) }}" class="btn btn-success">Купить билет</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-success">Войти для голосования и покупки</a>
                    @endauth
                    <a href="{{ route('afisha.index') }}" class="btn btn-outline-dark">Открыть афишу</a>
                </div>
            </div>
        </article>
    </div>
@endsection
