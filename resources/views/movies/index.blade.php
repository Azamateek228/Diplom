@extends('layouts.app')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <section class="page-hero compact-hero">
        <span class="eyebrow">Голосование и билеты</span>
        <h1>Фильмы выездного кинотеатра</h1>
        <p>Выберите город, поддержите фильм голосом и следите за заполненностью площадки.</p>
    </section>

    @auth
        @if(isset($userCityStats) && $userCityStats && $userCityStats['city'])
            <div class="user-city-panel">
                <div><strong>📍 Ваш город:</strong> {{ $userCityStats['city']->name }}</div>
                <div>Голосов в городе: <strong>{{ $userCityStats['votes_count'] }}</strong></div>
                <div>Ожидается зрителей: <strong>{{ $userCityStats['expected_attendees'] }}</strong></div>
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
            <div>
                <h3>Сейчас в прокате</h3>
                <p class="text-muted mb-0">Поиск и фильтры помогают быстро найти подходящий сеанс.</p>
            </div>
            <form method="GET" action="{{ route('movies.index') }}" class="movie-filter-panel">
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    class="form-control"
                    placeholder="Поиск по названию"
                >
                <select name="city_id" class="form-select">
                    <option value="">Все города</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" {{ ($filters['city_id'] ?? '') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                    @endforeach
                </select>
                <select name="genre" class="form-select">
                    <option value="">Все жанры</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre }}" {{ ($filters['genre'] ?? '') === $genre ? 'selected' : '' }}>{{ $genre }}</option>
                    @endforeach
                </select>
                <select name="age_rating" class="form-select">
                    <option value="">Все рейтинги</option>
                    @foreach ($ageRatings as $rating)
                        <option value="{{ $rating }}" {{ ($filters['age_rating'] ?? '') == $rating ? 'selected' : '' }}>{{ $rating }}+</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-main btn-sm">Применить</button>
                <a href="{{ route('movies.index') }}" class="btn btn-outline-dark btn-sm">Сбросить фильтры</a>
            </form>
        </div>

        @if ($movies->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🎬</div>
                <h4>Фильмы не найдены</h4>
                <p>По выбранным параметрам ничего не найдено. Измените название, жанр, возрастной рейтинг или город.</p>
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
                            <div class="movie-poster poster-fallback"><span>🎥</span><strong>{{ $movie->title }}</strong><small>Постер готовится</small></div>
                        @endif

                        <div class="info">
                            <div class="movie-card-header">
                                <h4>{{ $movie->title }}</h4>
                                <span class="movie-rating-badge">{{ $movie->age_rating }}+</span>
                            </div>

                            <p class="movie-description">{{ \Illuminate\Support\Str::limit($movie->description ?? 'Описание будет добавлено.', 130) }}</p>

                            <div class="movie-details">
                                <div class="detail-item"><span class="detail-label">Жанр:</span><span class="detail-value">{{ $movie->genre ?? 'Уточняется' }}</span></div>
                                <div class="detail-item"><span class="detail-label">Длительность:</span><span class="detail-value">{{ $movie->duration }} мин</span></div>
                                <div class="detail-item"><span class="detail-label">Город:</span><span class="detail-value">{{ $movie->city?->name ?? 'Все города' }}</span></div>
                                <div class="detail-item"><span class="detail-label">Площадка:</span><span class="detail-value">{{ $movie->venue ?? 'Уточняется' }}</span></div>
                                <div class="detail-item"><span class="detail-label">Дата показа:</span><span class="detail-value">{{ $movie->show_time?->format('d.m.Y H:i') ?? 'Уточняется' }}</span></div>
                            </div>

                            <div class="movie-stats-row">
                                <span>🗳️ {{ $movie->votes_count ?? 0 }} голосов</span>
                                <span>🎟️ {{ $movie->sold_tickets ?? 0 }} билетов</span>
                            </div>
                            <div class="movie-session-kpi">
                                <div class="session-meta">
                                    <span>Цена: <strong>{{ $ticketPrice }} ₽</strong></span>
                                    <span>Заполнено: <strong>{{ $movie->fill_percentage ?? 0 }}%</strong></span>
                                </div>
                                <div class="progress-bar-container"><div class="progress-bar" style="width: {{ $movie->fill_percentage ?? 0 }}%"></div></div>
                            </div>

                            <div class="movie-actions">
                                <a href="{{ route('movies.show', $movie) }}" class="btn btn-outline-dark btn-sm">Подробнее</a>
                                @auth
                                    <form method="POST" action="{{ route('votes.store') }}" class="vote-form">
                                        @csrf
                                        <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                        @if (!$movie->city_id)
                                            <select name="city_id" class="form-select" required>
                                                <option value="">Выберите город</option>
                                                @foreach ($cities as $city)
                                                    <option value="{{ $city->id }}" {{ auth()->user()->city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                        <input type="number" name="expected_attendees" class="form-control" min="1" max="10" value="1" title="Сколько зрителей придёт с вами">
                                        <button class="vote-btn" {{ $votingClosed ? 'disabled' : '' }}>{{ $votingClosed ? 'Закрыто' : 'Голосовать' }}</button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="vote-login-link">Войдите, чтобы голосовать</a>
                                @endauth
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
