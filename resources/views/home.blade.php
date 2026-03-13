@extends('layouts.app')

@section('content')
    <section class="hero text-center">
        <div class="container">
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
@endsection
