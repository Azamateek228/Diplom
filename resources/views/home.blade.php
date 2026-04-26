@extends('layouts.app')

@section('content')
    <section class="hero text-center dashboard-hero">
        <div class="container fade-in-up">
            <h1 class="display-3 fw-bold mb-4">
                Кинотеатр на колёсах
            </h1>
            <p class="lead mb-4">
                Мы привозим кино туда, где нет кинотеатров
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="/movies" class="btn btn-main btn-lg">
                    Выбрать фильм
                </a>
                <a href="/map" class="btn btn-outline-light btn-lg">
                    Наш маршрут
                </a>
            </div>
        </div>
    </section>

    <section class="home-kpi-section fade-in-up" style="animation-delay: 0.1s;">
        <div class="home-kpi-grid">
            <div class="home-kpi-card">
                <p class="home-kpi-label">Голоса</p>
                <p class="home-kpi-value">{{ $kpis['votes'] ?? 0 }}</p>
            </div>
            <div class="home-kpi-card">
                <p class="home-kpi-label">Куплено билетов</p>
                <p class="home-kpi-value">{{ $kpis['tickets'] ?? 0 }}</p>
            </div>
            <div class="home-kpi-card">
                <p class="home-kpi-label">Загрузка мест</p>
                <p class="home-kpi-value">{{ $kpis['load_percentage'] ?? 0 }}%</p>
                <div class="kpi-progress">
                    <div class="kpi-progress-bar" style="width: {{ $kpis['load_percentage'] ?? 0 }}%"></div>
                </div>
            </div>
            <div class="home-kpi-card">
                <p class="home-kpi-label">Текущий город тура</p>
                <p class="home-kpi-value home-kpi-city">{{ $kpis['current_city'] ?? 'Назначается' }}</p>
            </div>
        </div>
    </section>
@endsection
