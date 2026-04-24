@extends('layouts.app')

@section('content')
    @php
        $votingDeadline = \Carbon\Carbon::parse('2026-05-31 23:59:59');
        $isVotingClosed = now()->gt($votingDeadline);
    @endphp

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

    <div class="card mb-4 p-3 bg-light">
        <strong>🗓️ Голосование до: {{ $votingDeadline->format('d.m.Y H:i') }}</strong>
        <small class="text-muted">После этой даты новые голоса не принимаются.</small>
    </div>

    @auth
        @if(isset($userCityStats) && $userCityStats && $userCityStats['city'])
            <div class="card mb-4 p-3 bg-light">
                <h5 class="mb-2">📍 Ваш город: {{ $userCityStats['city']->name }}</h5>
                <div class="d-flex gap-4">
                    <span>Голосов: <strong>{{ $userCityStats['votes_count'] }}</strong></span>
                    <span>Ожидаемых зрителей: <strong>{{ $userCityStats['expected_attendees'] }}</strong></span>
                </div>
            </div>
        @endif
    @endauth

    <div class="movies">
        <div class="movies-header">
            <h3>Расписание (дата → город → фильм)</h3>

            <form method="GET" action="{{ route('movies.index') }}" class="city-filter">
                <select name="city_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Все города</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($movies->isEmpty())
            <div class="no-movies">
                <p>Фильмы не найдены</p>
            </div>
        @else
            <div class="movie-grid">
                @foreach ($movies as $movie)
                    @php
                        $ticketLimit = $movie->city?->ticketLimit() ?? 0;
                        $sold = (int) ($soldByMovie[$movie->id] ?? 0);
                        $available = max(0, $ticketLimit - $sold);
                    @endphp
                    <div class="movie-card">
                        <img class="movie-poster"
                            src="{{ $movie->poster ? asset($movie->poster) : asset('images/poster-placeholder.jpg') }}"
                            alt="{{ $movie->title }}">

                        <div class="info">
                            <h4>{{ $movie->title }}</h4>

                            <div class="movie-details">
                                @if ($movie->show_at)
                                    <div class="detail-item">
                                        <span class="detail-label">Дата:</span>
                                        <span class="detail-value">{{ $movie->show_at->format('d.m.Y H:i') }}</span>
                                    </div>
                                @endif
                                @if ($movie->city)
                                    <div class="detail-item">
                                        <span class="detail-label">Город:</span>
                                        <span class="detail-value">{{ $movie->city->name }}</span>
                                    </div>
                                @endif
                                @if ($movie->venue)
                                    <div class="detail-item">
                                        <span class="detail-label">Площадка:</span>
                                        <span class="detail-value">{{ $movie->venue }}</span>
                                    </div>
                                @endif
                                <div class="detail-item">
                                    <span class="detail-label">Цена:</span>
                                    <span class="detail-value">{{ $movie->ticket_price }} ₽</span>
                                </div>
                            </div>

                            <div class="votes-count">
                                <span>🗳️ Голосов: {{ $movie->votes_count ?? 0 }}</span>
                            </div>

                            @auth
                                @if(!$isVotingClosed)
                                    <form method="POST" action="{{ route('votes.store') }}" class="vote-form">
                                        @csrf
                                        <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                        @if (!$movie->city_id)
                                            <div class="vote-city-select mb-2">
                                                <label for="city_id_{{ $movie->id }}">Ваш город:</label>
                                                <select name="city_id" id="city_id_{{ $movie->id }}" class="form-select" required>
                                                    <option value="">Выберите город</option>
                                                    @foreach ($cities as $city)
                                                        <option value="{{ $city->id }}" {{ auth()->user()->city_id == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                        <button class="vote-btn">Голосовать</button>
                                    </form>
                                @endif

                                @if($movie->city)
                                    <form method="POST" action="{{ route('tickets.store', $movie) }}" class="mt-2">
                                        @csrf
                                        <label for="qty_{{ $movie->id }}">Покупка билетов</label>
                                        <select name="quantity" id="qty_{{ $movie->id }}" class="form-select mb-2">
                                            @for ($i = 1; $i <= min(10, $available); $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        <small class="text-muted d-block mb-2">Доступно: {{ $available }} из {{ $ticketLimit }} (зависит от города)</small>
                                        <button class="btn btn-primary" type="submit" {{ $available < 1 ? 'disabled' : '' }}>Купить билеты</button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
