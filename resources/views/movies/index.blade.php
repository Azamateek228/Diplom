@extends('layouts.app')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @auth
        @if(isset($userCityStats) && $userCityStats && $userCityStats['city'])
            <div class="card mb-4 p-3 bg-light">
                <h5 class="mb-2">📍 Ваш город: {{ $userCityStats['city']->name }}</h5>
                <div class="d-flex gap-4">
                    <span>Голосов: <strong>{{ $userCityStats['votes_count'] }}</strong></span>
                    <span>Ожидаемых зрителей: <strong>{{ $userCityStats['expected_attendees'] }}</strong></span>
                </div>
            </div>
        @endif
    @endauth

    <div class="movies">
        <div class="movies-header">
            <h3>Сейчас в прокате</h3>

            <form method="GET" action="{{ route('movies.index') }}" class="city-filter">
                <select name="city_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Все города</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($movies->isEmpty())
            <div class="no-movies">
                <p>Фильмы не найдены</p>
            </div>
        @else
            <div class="movie-grid">
                @foreach ($movies as $movie)
                    <div class="movie-card">
                        <img class="movie-poster"
                            src="{{ $movie->poster ? asset($movie->poster) : asset('images/poster-placeholder.jpg') }}"
                            alt="{{ $movie->title }}">

                        <div class="info">
                            <h4>{{ $movie->title }}</h4>

                            <div class="movie-details">
                                @if ($movie->genre)
                                    <div class="detail-item">
                                        <span class="detail-label">Жанр:</span>
                                        <span class="detail-value">{{ $movie->genre }}</span>
                                    </div>
                                @endif

                                @if ($movie->age_rating)
                                    <div class="detail-item">
                                        <span class="detail-label">Возраст:</span>
                                        <span class="detail-value">{{ $movie->age_rating }}+</span>
                                    </div>
                                @endif

                                @if ($movie->duration)
                                    <div class="detail-item">
                                        <span class="detail-label">Длительность:</span>
                                        <span class="detail-value">{{ $movie->duration }} мин</span>
                                    </div>
                                @endif

                                @if ($movie->venue)
                                    <div class="detail-item">
                                        <span class="detail-label">Площадка:</span>
                                        <span class="detail-value">{{ $movie->venue }}</span>
                                    </div>
                                @endif

                                @if ($movie->city)
                                    <div class="detail-item">
                                        <span class="detail-label">Город:</span>
                                        <span class="detail-value">{{ $movie->city->name }}</span>
                                    </div>
                                @endif

                                @if ($movie->description)
                                    <div class="detail-item description" data-movie-id="{{ $movie->id }}">
                                        <span class="detail-value">
                                            <span
                                                class="description-short">{{ Str::limit($movie->description, 100) }}</span>
                                            @if (strlen($movie->description) > 100)
                                                <span class="description-full"
                                                    style="display: none;">{{ $movie->description }}</span>
                                                <button type="button" class="toggle-description-btn"
                                                    onclick="toggleDescription({{ $movie->id }})">
                                                    <span class="show-more">Показать больше</span>
                                                    <span class="show-less" style="display: none;">Скрыть</span>
                                                </button>
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="votes-count">
                                <span>🗳️ Голосов: {{ $movie->votes_count ?? 0 }}</span>
                            </div>

                            @auth
                                <form method="POST" action="{{ route('votes.store') }}" class="vote-form">
                                    @csrf
                                    <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                    @if (!$movie->city_id)
                                        <div class="vote-city-select mb-2">
                                            <label for="city_id_{{ $movie->id }}">Ваш город:</label>
                                            <select name="city_id" id="city_id_{{ $movie->id }}" class="form-select"
                                                required>
                                                <option value="">Выберите город</option>
                                                @foreach ($cities as $city)
                                                    <option value="{{ $city->id }}" {{ auth()->user()->city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="mb-2">
                                        <label for="expected_{{ $movie->id }}">Сколько человек придёт?</label>
                                        <select name="expected_attendees" id="expected_{{ $movie->id }}" class="form-select">
                                            @for ($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <button class="vote-btn">Голосовать</button>
                                </form>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function toggleDescription(movieId) {
            const container = document.querySelector(`[data-movie-id="${movieId}"]`);
            if (!container) return;

            const short = container.querySelector('.description-short');
            const full = container.querySelector('.description-full');
            const showMore = container.querySelector('.show-more');
            const showLess = container.querySelector('.show-less');

            if (!short || !full || !showMore || !showLess) return;

            const isExpanded = short.style.display === 'none';

            if (isExpanded) {
                // Сворачиваем
                short.style.display = 'block';
                full.style.display = 'none';
                showMore.style.display = 'inline';
                showLess.style.display = 'none';
            } else {
                // Разворачиваем
                short.style.display = 'none';
                full.style.display = 'block';
                showMore.style.display = 'none';
                showLess.style.display = 'inline';
            }
        }
    </script>
@endsection
