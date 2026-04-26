@extends('layouts.app')

@section('content')
<div class="admin-form-container">
    <h2 class="mb-4">Редактировать город</h2>

    <form method="POST" action="{{ route('cities.update', $city) }}" class="city-form">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Название города *</label>
            <input type="text" id="name" name="name" class="form-control" 
                   placeholder="Введите название города" 
                   value="{{ old('name', $city->name) }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="lat">Широта (lat)</label>
                <input type="number" id="lat" name="lat" class="form-control" 
                       placeholder="55.7558" 
                       value="{{ old('lat', $city->lat) }}" 
                       step="any" min="-90" max="90">
                <small class="form-text">От -90 до 90 (например, для Москвы: 55.7558)</small>
            </div>

            <div class="form-group">
                <label for="lng">Долгота (lng)</label>
                <input type="number" id="lng" name="lng" class="form-control" 
                       placeholder="37.6173" 
                       value="{{ old('lng', $city->lng) }}" 
                       step="any" min="-180" max="180">
                <small class="form-text">От -180 до 180 (например, для Москвы: 37.6173)</small>
            </div>
            <div class="form-group">
                <label for="population">Население города</label>
                <input type="number" id="population" name="population" class="form-control"
                       placeholder="40000"
                       value="{{ old('population', $city->population ?? 40000) }}" min="1000">
                <small class="form-text">Влияет на лимит билетов в городе</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            <a href="{{ route('cities.index') }}" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>
@endsection
