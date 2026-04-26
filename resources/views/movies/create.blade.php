@extends('layouts.app')

@section('content')
    <div class="admin-form-container">
        <h2 class="mb-4">Добавить фильм</h2>

        <form method="POST" action="{{ route('movies.store') }}" class="movie-form">
            @csrf

            <div class="form-group">
                <label for="title">Название фильма *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="Введите название"
                    required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="genre">Жанр</label>
                    <input type="text" id="genre" name="genre" class="form-control"
                        placeholder="Комедия, Драма, Боевик">
                </div>

                <div class="form-group">
                    <label for="duration">Длительность (мин) *</label>
                    <input type="number" id="duration" name="duration" class="form-control" placeholder="120" required
                        min="1">
                </div>

                <div class="form-group">
                    <label for="age_rating">Возрастное ограничение *</label>
                    <select id="age_rating" name="age_rating" class="form-control" required>
                        <option value="">Выберите</option>
                        <option value="0">0+</option>
                        <option value="6">6+</option>
                        <option value="12">12+</option>
                        <option value="16">16+</option>
                        <option value="18">18+</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="city_id">Город</label>
                <select id="city_id" name="city_id" class="form-control">
                    <option value="">Выберите город</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="venue">Площадка показа</label>
                    <input type="text" id="venue" name="venue" class="form-control"
                        placeholder="Например: Центральная площадь">
                </div>

                <div class="form-group">
                    <label for="show_time">Время показа</label>
                    <input type="datetime-local" id="show_time" name="show_time" class="form-control">
                </div>

                <div class="form-group">
                    <label for="venue_capacity">Вместимость площадки</label>
                    <input type="number" id="venue_capacity" name="venue_capacity" class="form-control"
                        placeholder="150" min="1">
                </div>

                <div class="form-group">
                    <label for="expected_attendees">Ожидаемое количество зрителей</label>
                    <input type="number" id="expected_attendees" name="expected_attendees" class="form-control"
                        placeholder="100" min="0">
                </div>
            </div>

            <div class="form-group">
                <label for="poster">URL постера</label>
                <input type="text" id="poster" name="poster" class="form-control"
                    placeholder="https://example.com/poster.jpg">
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Краткое описание фильма"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить фильм</button>
                <a href="{{ route('admin.stats') }}" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
@endsection
