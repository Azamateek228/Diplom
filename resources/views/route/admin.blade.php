@extends('layouts.app')

@section('content')
    <div class="admin-route-page py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-5 fw-bold">🛣️ Управление маршрутом</h1>
                <a href="{{ route('route.index') }}" class="btn btn-outline-primary">
                    👁️ Просмотр маршрута
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">➕ Добавить маршрут</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('route.store') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="city_id" class="form-label">Город *</label>
                                    <select name="city_id" id="city_id" class="form-select" required>
                                        <option value="">Выберите город</option>
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="movie_id" class="form-label">Фильм *</label>
                                    <select name="movie_id" id="movie_id" class="form-select" required>
                                        <option value="">Выберите фильм</option>
                                        @foreach ($movies as $movie)
                                            <option value="{{ $movie->id }}">{{ $movie->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="visit_date" class="form-label">Дата посещения *</label>
                                    <input type="date" name="visit_date" id="visit_date" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="day_of_week" class="form-label">День недели *</label>
                                    <select name="day_of_week" id="day_of_week" class="form-select" required>
                                        <option value="">Выберите день</option>
                                        <option value="Понедельник">Понедельник</option>
                                        <option value="Вторник">Вторник</option>
                                        <option value="Среда">Среда</option>
                                        <option value="Четверг">Четверг</option>
                                        <option value="Пятница">Пятница</option>
                                        <option value="Суббота">Суббота</option>
                                        <option value="Воскресенье">Воскресенье</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="venue" class="form-label">Площадка</label>
                                    <input type="text" name="venue" id="venue" class="form-control" placeholder="Например: ДК 'Мир'">
                                </div>

                                <div class="mb-3">
                                    <label for="show_time" class="form-label">Время показа</label>
                                    <input type="time" name="show_time" id="show_time" class="form-control">
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    ➕ Добавить
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">📋 Существующие маршруты</h5>
                        </div>
                        <div class="card-body">
                            @if ($routes->isEmpty())
                                <p class="text-muted text-center">Маршрутов пока нет</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Дата</th>
                                                <th>День</th>
                                                <th>Город</th>
                                                <th>Фильм</th>
                                                <th>Площадка</th>
                                                <th>Время</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($routes as $route)
                                                <tr>
                                                    <td>{{ $route->visit_date->format('d.m.Y') }}</td>
                                                    <td>{{ $route->day_of_week }}</td>
                                                    <td>{{ $route->city->name }}</td>
                                                    <td>{{ $route->movie->title }}</td>
                                                    <td>{{ $route->venue ?? '-' }}</td>
                                                    <td>{{ $route->show_time ? date('H:i', strtotime($route->show_time)) : '-' }}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button" 
                                                                class="btn btn-warning" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editModal{{ $route->id }}">
                                                                ✏️
                                                            </button>
                                                            <form action="{{ route('route.destroy', $route) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить маршрут?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">🗑️</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Modal для редактирования -->
                                                <div class="modal fade" id="editModal{{ $route->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="{{ route('route.update', $route) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Редактировать маршрут</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Город</label>
                                                                        <select name="city_id" class="form-select" required>
                                                                            @foreach ($cities as $city)
                                                                                <option value="{{ $city->id }}" {{ $route->city_id == $city->id ? 'selected' : '' }}>
                                                                                    {{ $city->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Фильм</label>
                                                                        <select name="movie_id" class="form-select" required>
                                                                            @foreach ($movies as $movie)
                                                                                <option value="{{ $movie->id }}" {{ $route->movie_id == $movie->id ? 'selected' : '' }}>
                                                                                    {{ $movie->title }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Дата</label>
                                                                        <input type="date" name="visit_date" class="form-control" value="{{ $route->visit_date->format('Y-m-d') }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">День недели</label>
                                                                        <select name="day_of_week" class="form-select" required>
                                                                            <option value="Понедельник" {{ $route->day_of_week == 'Понедельник' ? 'selected' : '' }}>Понедельник</option>
                                                                            <option value="Вторник" {{ $route->day_of_week == 'Вторник' ? 'selected' : '' }}>Вторник</option>
                                                                            <option value="Среда" {{ $route->day_of_week == 'Среда' ? 'selected' : '' }}>Среда</option>
                                                                            <option value="Четверг" {{ $route->day_of_week == 'Четверг' ? 'selected' : '' }}>Четверг</option>
                                                                            <option value="Пятница" {{ $route->day_of_week == 'Пятница' ? 'selected' : '' }}>Пятница</option>
                                                                            <option value="Суббота" {{ $route->day_of_week == 'Суббота' ? 'selected' : '' }}>Суббота</option>
                                                                            <option value="Воскресенье" {{ $route->day_of_week == 'Воскресенье' ? 'selected' : '' }}>Воскресенье</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Площадка</label>
                                                                        <input type="text" name="venue" class="form-control" value="{{ $route->venue }}">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Время</label>
                                                                        <input type="time" name="show_time" class="form-control" value="{{ $route->show_time }}">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
