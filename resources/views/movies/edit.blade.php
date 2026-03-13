@extends('layouts.app')

@section('content')
    <div class="admin-form-container">
        <h2 class="mb-4">Редактировать фильм</h2>

        @if ($movie->poster)
            <div class="current-poster mb-4">
                <label>Текущий постер:</label>
                <img src="{{ $movie->poster }}" alt="{{ $movie->title }}"
                    style="max-width: 200px; border-radius: 10px; margin-top: 10px;">
            </div>
        @endif

        <form method="POST" action="{{ route('movies.update', $movie) }}" class="movie-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Название фильма *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="Введите название"
                    value="{{ old('title', $movie->title) }}" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="genre">Жанр</label>
                    <input type="text" id="genre" name="genre" class="form-control"
                        placeholder="Комедия, Драма, Боевик" value="{{ old('genre', $movie->genre) }}">
                </div>

                <div class="form-group">
                    <label for="duration">Длительность (мин) *</label>
                    <input type="number" id="duration" name="duration" class="form-control" placeholder="120"
                        value="{{ old('duration', $movie->duration) }}" required min="1">
                </div>

                <div class="form-group">
                    <label for="age_rating">Возрастное ограничение *</label>
                    <select id="age_rating" name="age_rating" class="form-control" required>
                        <option value="">Выберите</option>
                        <option value="0" {{ old('age_rating', $movie->age_rating) == '0' ? 'selected' : '' }}>0+
                        </option>
                        <option value="6" {{ old('age_rating', $movie->age_rating) == '6' ? 'selected' : '' }}>6+
                        </option>
                        <option value="12" {{ old('age_rating', $movie->age_rating) == '12' ? 'selected' : '' }}>12+
                        </option>
                        <option value="16" {{ old('age_rating', $movie->age_rating) == '16' ? 'selected' : '' }}>16+
                        </option>
                        <option value="18" {{ old('age_rating', $movie->age_rating) == '18' ? 'selected' : '' }}>18+
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="city_id">Город</label>
                <select id="city_id" name="city_id" class="form-control">
                    <option value="">Выберите город</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}"
                            {{ old('city_id', $movie->city_id) == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="venue">Площадка показа</label>
                    <input type="text" id="venue" name="venue" class="form-control"
                        placeholder="Например: Центральная площадь" value="{{ old('venue', $movie->venue) }}">
                </div>

                <div class="form-group">
                    <label for="expected_attendees">Ожидаемое количество зрителей</label>
                    <input type="number" id="expected_attendees" name="expected_attendees" class="form-control"
                        placeholder="100" value="{{ old('expected_attendees', $movie->expected_attendees) }}"
                        min="0">
                </div>
            </div>

            <div class="form-group">
                <label for="poster">URL постера</label>
                <input type="text" id="poster" name="poster" class="form-control"
                    placeholder="https://example.com/poster.jpg" value="{{ old('poster', $movie->poster) }}">
                <small class="form-text text-muted">Введите URL изображения для обновления постера</small>
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Краткое описание фильма">{{ old('description', $movie->description) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="{{ route('admin.stats') }}" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
@endsection
