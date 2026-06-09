@extends('layouts.app')

@section('content')
    <div class="movie-show-page">
        <a href="{{ route('movies.index') }}" class="btn btn-outline-dark btn-sm mb-3">← Назад к фильмам</a>

        <article class="movie-show-card">
            <div class="movie-show-poster">
                @if($movie->poster)
                    <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
                @else
                    <div class="poster-fallback poster-fallback--large"><span>🎬</span><strong>{{ $movie->title }}</strong><small>Афиша скоро появится</small></div>
                @endif
            </div>

            <div class="movie-show-info">
                <span class="eyebrow">Карточка фильма</span>
                <h1>{{ $movie->title }}</h1>
                <p class="lead-text">{{ $movie->description ?? 'Описание фильма появится позже.' }}</p>

                @if (($userVoteMovieId ?? null) === $movie->id)
                    <span class="selected-vote-badge">Вы уже выбрали этот фильм в своём городе</span>
                @endif

                <div class="show-meta-grid">
                    <div><span>Жанр</span><strong>{{ $movie->genre ?? 'Уточняется' }}</strong></div>
                    <div><span>Возраст</span><strong>{{ $movie->age_rating }}+</strong></div>
                    <div><span>Длительность</span><strong>{{ $movie->duration }} мин</strong></div>
                    <div><span>Голоса</span><strong>{{ $movie->votes_count ?? 0 }}</strong></div>
                </div>

                <div class="movie-session-kpi show-kpi">
                    <div class="session-meta">
                        <span>Продано билетов: <strong>{{ $soldTickets }}</strong></span>
                        <span>Показы и покупка билетов доступны в афише</span>
                    </div>
                    <div class="progress-bar-container"><div class="progress-bar" style="--progress: {{ $fillPercentage }}%"></div></div>
                </div>

                <div class="show-actions">
                    @auth
                        <form method="POST" action="{{ route('votes.store') }}" class="vote-form vote-form-inline">
                            @csrf
                            <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                            @if (auth()->user()->city_id)
                                <button type="submit" class="vote-btn" {{ $votingClosed ? 'disabled' : '' }}>{{ $votingClosed ? 'Голосование закрыто' : (($userVoteMovieId ?? null) === $movie->id ? 'Обновить голос' : 'Голосовать за фильм') }}</button>
                                @if ($votingClosed)
                                    <span class="vote-help-text">Голосование закрыто: маршрут уже формируется.</span>
                                @endif
                            @else
                                <span class="vote-login-link">Выберите город в профиле, чтобы голосовать. <a href="{{ route('profile.edit') }}">Открыть профиль</a></span>
                            @endif
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-success">Войти для голосования и покупки</a>
                    @endauth
                    <a href="{{ route('afisha.index') }}" class="btn btn-outline-dark">Открыть афишу</a>
                </div>
            </div>
        </article>
    </div>
@endsection
