@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 760px;">
        <h2 class="mb-4">Оплата билетов</h2>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Детали сеанса</h5>
                <p class="mb-1"><strong>Город:</strong> {{ $city->name }}</p>
                <p class="mb-1"><strong>Фильм:</strong> {{ $movie->title }}</p>
                <p class="mb-1"><strong>Дата:</strong> {{ \Carbon\Carbon::parse($showDate)->format('d.m.Y') }}</p>
                <p class="mb-1"><strong>Время:</strong> {{ $showTime }}</p>
                <p class="mb-1"><strong>Цена:</strong> {{ $ticketPrice }} ₽ / билет</p>
                <p class="mb-0"><strong>Осталось билетов:</strong> {{ $availableTickets }} из {{ $maxTickets }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Форма оплаты</h5>
                <form method="POST" action="{{ route('tickets.store') }}">
                    @csrf
                    <input type="hidden" name="city_id" value="{{ $city->id }}">
                    <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                    <input type="hidden" name="show_date" value="{{ $showDate }}">
                    <input type="hidden" name="show_time" value="{{ $showTime }}">

                    <div class="mb-3">
                        <label class="form-label">Количество билетов</label>
                        <input type="number" class="form-control" name="quantity" min="1" max="{{ max(1, $availableTickets) }}" value="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Номер карты</label>
                        <input type="text" class="form-control" name="card_number" placeholder="0000 0000 0000 0000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Держатель карты</label>
                        <input type="text" class="form-control" name="card_holder" placeholder="IVAN IVANOV" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Срок действия</label>
                            <input type="text" class="form-control" name="card_expiry" placeholder="MM/YY" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CVV</label>
                            <input type="password" class="form-control" name="card_cvv" placeholder="123" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-4">Оплатить</button>
                </form>
            </div>
        </div>
    </div>
@endsection
