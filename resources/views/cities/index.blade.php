@extends('layouts.app')

@section('content')
    <div class="admin-page">
        <div class="admin-header">
            <h2 class="mb-4">Управление городами</h2>
            <a href="{{ route('cities.create') }}" class="btn btn-success">
                Добавить город
            </a>
        </div>

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

        @if ($cities->isEmpty())
            <div class="no-data">
                <p>Города не добавлены</p>
                <a href="{{ route('cities.create') }}" class="btn btn-primary">Добавить первый город</a>
            </div>
        @else
            <div class="cities-admin-grid">
                @foreach ($cities as $city)
                    <div class="city-admin-card">
                        <div class="city-admin-header">
                            <h4>{{ $city->name }}</h4>
                            <div class="city-badges">
                                @if ($city->lat && $city->lng)
                                    <span class="badge badge-success">Координаты</span>
                                @else
                                    <span class="badge badge-warning">⚠️ Без координат</span>
                                @endif
                                @if ($city->votes_count > 0)
                                    <span class="badge badge-info">{{ $city->votes_count }} голосов</span>
                                @endif
                            </div>
                        </div>

                        <div class="city-admin-details">
                            @if ($city->population)
                                <div class="detail-row">
                                    <span class="label">Население:</span>
                                    <span class="value">{{ number_format($city->population, 0, ',', ' ') }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Лимит билетов:</span>
                                    <span class="value">{{ $city->ticketLimit() }}</span>
                                </div>
                            @endif

                            @if ($city->lat && $city->lng)
                                <div class="detail-row">
                                    <span class="label">Широта:</span>
                                    <span class="value">{{ $city->lat }}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Долгота:</span>
                                    <span class="value">{{ $city->lng }}</span>
                                </div>
                            @else
                                <div class="detail-row">
                                    <span class="value" style="color: #888;">Координаты не указаны</span>
                                </div>
                            @endif
                        </div>

                        <div class="city-admin-actions">
                            <a href="{{ route('cities.edit', $city) }}" class="btn-edit">
                                Редактировать
                            </a>
                            <form method="POST" action="{{ route('cities.destroy', $city) }}" class="delete-form"
                                onsubmit="return confirm('Вы уверены, что хотите удалить город {{ $city->name }}? Это действие нельзя отменить.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    Удалить
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection
