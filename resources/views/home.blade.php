@extends('layouts.seo')

@php
    $seoTitle = 'Кинотеатр на колёсах — выездной кинотеатр в вашем городе | Афиша мероприятий';
    $seoDescription = 'Уникальный выездной кинотеатр на колёсах в Татарстане. Голосуйте за фильмы, выбирайте маршрут следования. Показ фильмов под открытым небом. Бесплатные показы для всех желающих.';
    $seoKeywords = 'кинотеатр на колёсах, выездной кинотеатр, мобильный кинотеатр, кино под открытым небом, афиша мероприятий, голосование за фильмы, бесплатный кинотеатр, Татарстан';
@endphp

@section('title', $seoTitle)
@section('description', $seoDescription)
@section('keywords', $seoKeywords)

@section('content')
    <section class="hero text-center py-5" aria-labelledby="hero-title">
        <div class="container">
            <h1 id="hero-title" class="display-3 fw-bold mb-4">
                🎬 Кинотеатр на колёсах
            </h1>
            <p class="lead mb-4 fs-4">
                Мы привозим кино туда, где нет кинотеатров
            </p>
            <p class="mb-4 text-muted">
                Уникальный проект мобильного кинотеатра в Татарстане. 
                Голосуйте за любимые фильмы и выбирайте маршрут нашего кинотеатра!
            </p>

            <div class="d-flex justify-content-center gap-3 flex-wrap" role="group" aria-label="Основные действия">
                <a href="{{ route('movies.index') }}" class="btn btn-main btn-lg" title="Посмотреть афишу фильмов">
                    🎬 Афиша
                </a>
                <a href="{{ route('route.index') }}" class="btn btn-main btn-lg" title="Расписание показов на неделю">
                    🗓️ Маршрут на неделю
                </a>
                <a href="/map" class="btn btn-outline-light btn-lg" title="Карта маршрута кинотеатра">
                    🗺️ Карта маршрута
                </a>
            </div>
            
            <div class="row mt-5 pt-4">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-4 mb-3">🎯</div>
                            <h3 class="h5 fw-bold">Голосуйте за фильмы</h3>
                            <p class="text-muted small">
                                Выбирайте лучшие фильмы и влияйте на наш репертуар
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-4 mb-3">📍</div>
                            <h3 class="h5 fw-bold">Выбирайте маршрут</h3>
                            <p class="text-muted small">
                                Определяйте, в какой город приедет кинотеатр
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-4 mb-3">🎉</div>
                            <h3 class="h5 fw-bold">Бесплатные показы</h3>
                            <p class="text-muted small">
                                Посещение всех сеансов совершенно бесплатно
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    {{-- Микроразметка для главной страницы --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Кинотеатр на колёсах — главная страница",
        "description": "Уникальный выездной кинотеатр на колёсах в Татарстане. Голосуйте за фильмы и выбирайте маршрут.",
        "url": "{{ url('/') }}",
        "inLanguage": "ru",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Кинотеатр на колёсах",
            "url": "{{ url('/') }}"
        }
    }
    </script>
@endsection
