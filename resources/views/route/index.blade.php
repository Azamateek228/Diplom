@extends('layouts.seo')

@php
    $seoTitle = 'Маршрут кинотеатра на неделю — расписание показов фильмов';
    $seoDescription = 'Актуальное расписание выездного кинотеатра на неделю. Узнайте, когда и где состоятся показы фильмов в вашем городе.';
    $seoKeywords = 'маршрут кинотеатра, расписание показов, афиша на неделю, выездной кинотеатр, показ фильмов, кино в городе';
@endphp

@section('title', $seoTitle)
@section('description', $seoDescription)
@section('keywords', $seoKeywords)

@section('content')
    <div class="route-page py-5" aria-labelledby="route-title">
        <div class="container">
            <header class="text-center mb-5">
                <h1 id="route-title" class="display-4 fw-bold">🗓️ Маршрут на неделю</h1>
                <p class="lead text-muted">Расписание показа фильмов в городах Татарстана</p>
                <p class="text-muted small">
                    Следите за нашим маршрутом и не пропустите бесплатные показы фильмов в вашем городе
                </p>
            </header>

            @if ($sortedRoutes->isEmpty())
                <div class="text-center py-5" role="status">
                    <div class="display-1" aria-hidden="true">🚐</div>
                    <h2 class="h3 mt-3">Маршрут формируется</h2>
                    <p class="text-muted">Скоро здесь появится расписание показов</p>
                    <a href="{{ route('movies.index') }}" class="btn btn-outline-primary mt-3">
                        🎬 Смотреть афишу
                    </a>
                </div>
            @else
                <section aria-label="Расписание показов по дням">
                    @foreach ($sortedRoutes as $day => $routes)
                        <article class="day-card card mb-4 shadow-sm" aria-labelledby="day-{{ Str::slug($day) }}">
                            <header class="card-header bg-primary text-white">
                                <h2 id="day-{{ Str::slug($day) }}" class="h3 mb-0 fw-bold">📅 {{ $day }}</h2>
                            </header>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach ($routes as $route)
                                        <div class="col-md-6 col-lg-4">
                                            <article class="route-item card h-100 border-0 shadow-sm" 
                                                     aria-labelledby="route-{{ $route->id }}">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <div class="flex-shrink-0" aria-hidden="true">
                                                            <span class="badge bg-primary fs-6">🎬</span>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h3 id="route-{{ $route->id }}" class="h5 card-title mb-1">
                                                                {{ $route->movie->title }}
                                                            </h3>
                                                            @if ($route->show_time)
                                                                <p class="text-muted mb-0 small">
                                                                    <time datetime="{{ $route->show_time }}">
                                                                        ⏰ {{ date('H:i', strtotime($route->show_time)) }}
                                                                    </time>
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="route-details mt-3">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="me-2" aria-hidden="true">📍</span>
                                                            <span class="fw-bold">{{ $route->city->name }}</span>
                                                        </div>

                                                        @if ($route->venue)
                                                            <div class="d-flex align-items-center mb-2">
                                                                <span class="me-2" aria-hidden="true">🏛️</span>
                                                                <span class="text-muted">{{ $route->venue }}</span>
                                                            </div>
                                                        @endif

                                                        @if ($route->movie->genre)
                                                            <div class="mt-2">
                                                                <span class="badge bg-secondary">{{ $route->movie->genre }}</span>
                                                            </div>
                                                        @endif

                                                        @if ($route->movie->age_rating)
                                                            <div class="mt-1">
                                                                <span class="badge bg-warning text-dark">{{ $route->movie->age_rating }}+</span>
                                                            </div>
                                                        @endif

                                                        @if ($route->movie->duration)
                                                            <div class="mt-1">
                                                                <span class="badge bg-info">{{ $route->movie->duration }} мин</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if ($route->movie->description)
                                                        <p class="card-text mt-3 small text-muted">
                                                            {{ Str::limit($route->movie->description, 100) }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </article>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif

            <div class="text-center mt-5">
                <a href="/map" class="btn btn-outline-primary btn-lg" title="Посмотреть карту маршрута">
                    🗺️ Посмотреть карту маршрута
                </a>
                <a href="{{ route('movies.index') }}" class="btn btn-outline-secondary btn-lg ms-2" title="Перейти к афише">
                    🎬 Афиша
                </a>
            </div>
        </div>
    </div>

    <style>
        .route-page {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        .day-card {
            border-radius: 15px;
            overflow: hidden;
        }

        .day-card .card-header {
            border-radius: 0;
            padding: 1.25rem 1.5rem;
        }

        .route-item {
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .route-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
        }

        .route-item .card-body {
            padding: 1.25rem;
        }

        .route-details {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }

        @media (max-width: 768px) {
            .route-page h1 {
                font-size: 1.75rem;
            }

            .day-card .card-header h3 {
                font-size: 1.25rem;
            }
        }
    </style>
@endsection
