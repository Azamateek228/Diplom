@extends('layouts.app')

@section('content')
    <section class="page-hero compact-hero">
        <span class="eyebrow">Голосование и билеты</span>
        <h1>Фильмы выездного кинотеатра</h1>
        <p>Выберите фильм из общего каталога и поддержите его голосом от своего города.</p>
    </section>

    @auth
        @if(isset($userCityStats) && $userCityStats && $userCityStats['city'])
            <div class="user-city-panel">
                <div><strong>Ваш город:</strong> {{ $userCityStats['city']->name }}</div>
                <div>Голосов в вашем городе: <strong>{{ $userCityStats['votes_count'] }}</strong></div>
                <div class="small text-muted">Ваш голос будет учтён именно в выбранном городе.</div>
            </div>
        @endif
    @else
        <div class="login-callout">
            <div><strong>Хотите повлиять на маршрут?</strong><br>Войдите, чтобы голосовать за фильмы и покупать билеты.</div>
            <a href="{{ route('login') }}" class="btn btn-success btn-sm">Войти</a>
        </div>
    @endauth

    @if (!empty($votingDeadline))
        <div class="alert {{ $votingClosed ? 'alert-danger' : 'alert-info' }} mb-3">
            Дедлайн голосования: {{ $votingDeadline->format('d.m.Y H:i') }}{{ $votingClosed ? ' — голосование закрыто.' : '' }}
        </div>
    @endif

    <div class="movies">
        <div class="movies-header movies-header--stacked">
            <div class="catalog-heading-row">
                <div>
                    <h3>Сейчас в прокате</h3>
                    <p class="text-muted mb-0">Поиск и фильтры помогают быстро найти подходящий сеанс.</p>
                </div>
                @if($movies->total() > 0)
                    <span class="result-counter">Найдено: {{ $movies->total() }} фильмов</span>
                @endif
            </div>
            <form method="GET" action="{{ route('movies.index') }}" class="movie-filter-panel catalog-filter-panel">
                <label class="filter-field filter-field--wide">
                    <span>Название</span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control"
                        placeholder="Поиск по названию"
                    >
                </label>
                <label class="filter-field">
                    <span>Жанр</span>
                    <select name="genre" class="form-select">
                        <option value="">Все жанры</option>
                        @foreach ($genres as $genre)
                            <option value="{{ $genre }}" {{ ($filters['genre'] ?? '') === $genre ? 'selected' : '' }}>{{ $genre }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="filter-field">
                    <span>Возраст</span>
                    <select name="age_rating" class="form-select">
                        <option value="">Все рейтинги</option>
                        @foreach ($ageRatings as $rating)
                            <option value="{{ $rating }}" {{ ($filters['age_rating'] ?? '') == $rating ? 'selected' : '' }}>{{ $rating }}+</option>
                        @endforeach
                    </select>
                </label>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Применить</button>
                    <a href="{{ route('movies.index') }}" class="btn btn-secondary">Сбросить</a>
                </div>
            </form>
            @if(($activeFiltersCount ?? 0) > 0)
                <div class="active-filter-chips">
                    @if(!empty($filters['search']))<span class="filter-chip">Поиск: {{ $filters['search'] }}</span>@endif
                    @if(!empty($filters['genre']))<span class="filter-chip">Жанр: {{ $filters['genre'] }}</span>@endif
                    @if(!empty($filters['age_rating']))<span class="filter-chip">Возраст: {{ $filters['age_rating'] }}+</span>@endif
                </div>
            @endif
        </div>

        @if ($movies->isEmpty())
            <div class="empty-state">
                <h4>Фильмы не найдены</h4>
                <p>По выбранным параметрам ничего не найдено. Измените название, жанр или возрастной рейтинг.</p>
                <div class="empty-state-actions">
                    <a href="{{ route('movies.index') }}" class="btn btn-main btn-sm">Сбросить фильтры</a>
                    <a href="{{ route('afisha.index') }}" class="btn btn-outline-dark btn-sm">Открыть афишу</a>
                </div>
            </div>
        @else
            <div class="movie-grid">
                @foreach ($movies as $movie)
                    <article class="movie-card fade-in-up">
                        @if($movie->poster)
                            <img class="movie-poster" src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
                        @else
                            <div class="movie-poster poster-fallback"><strong>{{ $movie->title }}</strong><small>Афиша скоро появится</small></div>
                        @endif

                        <div class="info">
                            <div class="movie-card-header">
                                <h4>{{ $movie->title }}</h4>
                                <span class="movie-rating-badge">{{ $movie->age_rating }}+</span>
                            </div>
                            @if (($userVoteMovieId ?? null) === $movie->id)
                                <span class="selected-vote-badge">Ваш выбор в городе</span>
                            @endif

                            <p class="movie-description">{{ \Illuminate\Support\Str::limit($movie->description ?? 'Описание будет добавлено.', 130) }}</p>

                            <div class="movie-details">
                                <div class="detail-item"><span class="detail-label">Жанр:</span><span class="detail-value">{{ $movie->genre ?? 'Уточняется' }}</span></div>
                                <div class="detail-item"><span class="detail-label">Длительность:</span><span class="detail-value">{{ $movie->duration }} мин</span></div>
                            </div>

                            <div class="movie-stats-row">
                                <span>Голосов: <strong>{{ $movie->votes_count ?? 0 }}</strong></span>
                                <span>Куплено билетов: <strong>{{ $movie->sold_tickets ?? 0 }}</strong></span>
                            </div>
                            <div class="movie-session-kpi">
                                <div class="session-meta">
                                    <span>Фильм участвует в голосовании по всем городам</span>
                                    <span>Билеты покупаются в афише на конкретный показ</span>
                                </div>
                            </div>

                            <div class="movie-actions">
                                <a href="{{ route('movies.show', $movie) }}" class="btn btn-outline-dark btn-sm">Подробнее</a>
                                @auth
                                    @if (auth()->user()->city_id)
                                        <form method="POST" action="{{ route('votes.store') }}" class="vote-form">
                                            @csrf
                                            <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                            <button type="submit" class="vote-btn" {{ $votingClosed ? 'disabled' : '' }}>{{ $votingClosed ? 'Голосование закрыто' : (($userVoteMovieId ?? null) === $movie->id ? 'Изменить голос' : 'Голосовать') }}</button>
                                            @if ($votingClosed)
                                                <span class="vote-help-text">Дедлайн голосования прошёл.</span>
                                            @endif
                                        </form>
                                    @else
                                        <div class="vote-login-link">
                                            Выберите город в профиле, чтобы голосовать.
                                            <a href="{{ route('profile.edit') }}">Открыть профиль</a>
                                        </div>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="vote-login-link">Войдите, чтобы голосовать</a>
                                @endauth
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="cinema-pagination catalog-pagination">
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
@endsection
