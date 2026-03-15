@extends('layouts.seo')

@php
    $currentCityFilter = request('city_id') ? $cities->firstWhere('id', request('city_id')) : null;
    
    if ($currentCityFilter) {
        $seoTitle = "Афиша фильмов в городе {$currentCityFilter->name} | Кинотеатр на колёсах";
        $seoDescription = "Голосуйте за фильмы в городе {$currentCityFilter->name}. Актуальная афиша выездного кинотеатра. Выбирайте лучшие фильмы для показа под открытым небом.";
        $seoKeywords = "афиша {$currentCityFilter->name}, фильмы {$currentCityFilter->name}, голосование за фильмы, кинотеатр {$currentCityFilter->name}, кино под открытым небом";
        $canonical = route('movies.index', ['city_id' => $currentCityFilter->id]);
    } else {
        $seoTitle = 'Афиша фильмов — голосуйте за лучшие фильмы | Кинотеатр на колёсах';
        $seoDescription = 'Актуальная афиша выездного кинотеатра. Голосуйте за фильмы, которые хотите увидеть. Рейтинг фильмов формируется зрителями.';
        $seoKeywords = 'афиша фильмов, голосование за фильмы, рейтинг фильмов, кинотеатр, кино, фильмы 2026';
        $canonical = route('movies.index');
    }
@endphp

@section('title', $seoTitle)
@section('description', $seoDescription)
@section('keywords', $seoKeywords)
@php $canonical = $canonical ?? route('movies.index'); @endphp

@section('content')
    <div class="afisha py-5" aria-labelledby="afisha-title">
        <header class="afisha-header text-center mb-5">
            <div class="container">
                <h1 id="afisha-title" class="display-4 fw-bold mb-2">🎬 АФИША</h1>
                <p class="lead text-muted">Фильмы, которые вы выбираете сами</p>
                <p class="text-muted small">
                    @if ($currentCityFilter)
                        Голосование за фильмы в городе <strong>{{ $currentCityFilter->name }}</strong>
                    @else
                        Выберите город для просмотра афиши или смотрите все фильмы
                    @endif
                </p>

                <form method="GET" action="{{ route('movies.index') }}" class="city-filter mt-3" role="search" aria-label="Фильтр по городам">
                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-4">
                            <label for="city-select" class="visually-hidden">Выберите город</label>
                            <select name="city_id" id="city-select" class="form-select form-select-lg" onchange="this.form.submit()" aria-describedby="city-filter-help">
                                <option value="">🏙️ Все города</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small id="city-filter-help" class="visually-hidden">
                                Выберите город из списка для фильтрации афиши
                            </small>
                        </div>
                    </div>
                </form>
            </div>
        </header>

        <div class="container">
            @if ($movies->isEmpty())
                <div class="no-movies text-center py-5" role="status">
                    <div class="display-1" aria-hidden="true">🎭</div>
                    <h2 class="h4 mt-3">Фильмы не найдены</h2>
                    <p class="text-muted">
                        @if ($currentCityFilter)
                            В городе {{ $currentCityFilter->name }} пока нет фильмов. 
                            <a href="{{ route('movies.index') }}">Показать все фильмы</a>
                        @else
                            В ближайшее время здесь появятся фильмы для голосования
                        @endif
                    </p>
                </div>
            @else
                <section aria-label="Список фильмов">
                    <p class="visually-hidden">
                        Показано {{ $movies->count() }} фильмов для голосования. 
                        Каждый фильм имеет рейтинг, описание и возможность проголосовать.
                    </p>
                    
                    <div class="movie-grid row g-4">
                        @foreach ($movies as $movie)
                            <article class="col-md-6 col-lg-4">
                                <div class="movie-card card h-100 shadow-sm" 
                                     aria-labelledby="movie-title-{{ $movie->id }}">
                                    
                                    <span class="movie-rank position-absolute top-0 start-0 bg-primary text-white fw-bold px-3 py-2 m-2 rounded" 
                                          aria-label="Место в рейтинге: {{ $loop->iteration }}">
                                        #{{ $loop->iteration }}
                                    </span>

                                    <img class="movie-poster card-img-top"
                                        src="{{ $movie->poster ? asset($movie->poster) : asset('images/poster-placeholder.jpg') }}"
                                        alt="Постер фильма: {{ $movie->title }}"
                                        loading="lazy"
                                        style="height: 400px; object-fit: cover;">

                                    <div class="card-body d-flex flex-column">
                                        <h2 id="movie-title-{{ $movie->id }}" class="card-title mb-2 h5">
                                            {{ $movie->title }}
                                        </h2>

                                        <div class="movie-meta mb-3" aria-label="Информация о фильме">
                                            @if ($movie->genre)
                                                <span class="badge bg-secondary me-1" title="Жанр">{{ $movie->genre }}</span>
                                            @endif
                                            @if ($movie->age_rating)
                                                <span class="badge bg-warning text-dark" title="Возрастное ограничение">{{ $movie->age_rating }}+</span>
                                            @endif
                                            @if ($movie->duration)
                                                <span class="badge bg-info" title="Продолжительность">{{ $movie->duration }} мин</span>
                                            @endif
                                        </div>

                                        <div class="movie-rating mb-3" aria-label="Рейтинг фильма">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-bold">🔥 Рейтинг:</span>
                                                <span class="badge bg-danger fs-6" aria-label="{{ $movie->rating ?? 0 }} голосов">
                                                    {{ $movie->rating ?? 0 }}
                                                </span>
                                            </div>
                                        </div>

                                        @if ($movie->description)
                                            <p class="card-text text-muted small flex-grow-1">
                                                {{ Str::limit($movie->description, 120) }}
                                            </p>
                                        @endif

                                        @if ($movie->city)
                                            <div class="movie-location mb-2 text-muted small" 
                                                 aria-label="Место показа">
                                                📍 {{ $movie->city->name }}
                                                @if ($movie->venue)
                                                    — {{ $movie->venue }}
                                                @endif
                                            </div>
                                        @endif

                                        @auth
                                            <form method="POST" action="{{ route('votes.store') }}" 
                                                  class="vote-form mt-3" 
                                                  aria-label="Форма голосования за фильм {{ $movie->title }}">
                                                @csrf
                                                <input type="hidden" name="movie_id" value="{{ $movie->id }}">

                                                @if (!$movie->city_id)
                                                    <div class="mb-2">
                                                        <label for="city_id_{{ $movie->id }}" class="form-label small">
                                                            Ваш город:
                                                        </label>
                                                        <select name="city_id" id="city_id_{{ $movie->id }}" 
                                                                class="form-select form-select-sm" required>
                                                            <option value="">Выберите город</option>
                                                            @foreach ($cities as $city)
                                                                <option value="{{ $city->id }}" 
                                                                        {{ auth()->user()->city_id == $city->id ? 'selected' : '' }}>
                                                                    {{ $city->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif

                                                <div class="mb-2">
                                                    <label for="expected_{{ $movie->id }}" class="form-label small">
                                                        Сколько человек придёт?
                                                    </label>
                                                    <select name="expected_attendees" id="expected_{{ $movie->id }}" 
                                                            class="form-select form-select-sm">
                                                        @for ($i = 1; $i <= 10; $i++)
                                                            <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>
                                                                {{ $i }}
                                                            </option>
                                                        @endfor
                                                    </select>
                                                </div>

                                                <button type="submit" class="btn btn-primary w-100 mt-2">
                                                    🗳️ Голосовать (+1 к рейтингу)
                                                </button>
                                            </form>
                                        @else
                                            <a href="/login" class="btn btn-outline-primary w-100 mt-3" 
                                               title="Войдите, чтобы голосовать за фильмы">
                                                🔐 Войдите, чтобы голосовать
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    <style>
        .afisha {
            padding: 2rem 0;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
        }

        .afisha-header {
            color: #fff;
        }

        .afisha-header .text-muted {
            color: #b0b0b0 !important;
        }

        .movie-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
        }

        .movie-rank {
            z-index: 10;
        }

        .movie-poster {
            height: 350px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .movie-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .movie-card .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 0.75rem;
        }

        .movie-meta .badge {
            font-weight: 500;
            font-size: 0.75rem;
        }

        .movie-rating {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .movie-location {
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            margin-top: auto;
        }

        .vote-form {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }

        .vote-form .btn-primary {
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .vote-form .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            .movie-poster {
                height: 280px;
            }

            .afisha-header h1 {
                font-size: 2rem;
            }
        }
    </style>
@endsection
