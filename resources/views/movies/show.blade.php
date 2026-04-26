@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 900px;">
        <a href="{{ route('afisha.index') }}" class="btn btn-outline-secondary btn-sm mb-3">Назад к афише</a>
        <div class="card">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="{{ $movie->poster ? asset($movie->poster) : asset('images/poster-placeholder.jpg') }}"
                        alt="{{ $movie->title }}" class="img-fluid rounded-start">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h3 class="card-title">{{ $movie->title }}</h3>
                        <p class="mb-1"><strong>Возраст:</strong> {{ $movie->age_rating }}+</p>
                        <p class="mb-1"><strong>Длительность:</strong> {{ $movie->duration }} мин</p>
                        <p class="mb-1"><strong>Площадка:</strong> {{ $movie->venue ?? 'Уточняется' }}</p>
                        <p class="mb-1"><strong>Время:</strong> {{ $movie->show_time ? \Carbon\Carbon::parse($movie->show_time)->format('d.m.Y H:i') : 'Уточняется' }}</p>
                        <p class="mt-3 mb-0">{{ $movie->description ?? 'Описание будет добавлено.' }}</p>
                        <div class="alert alert-secondary mt-3 mb-0">
                            Блок с трейлером добавим на следующем этапе.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
