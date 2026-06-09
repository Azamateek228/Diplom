@extends('layouts.app')

@section('content')
    <section class="payment-page">
        <div class="page-hero compact-hero payment-hero">
            <span class="eyebrow">Безопасная демонстрация</span>
            <h1>Оплата демо-билетов</h1>
            <p>Форма имитирует банковскую оплату для защиты диплома: деньги не списываются, CVV и полный номер карты не сохраняются.</p>
        </div>

        <div class="payment-layout">
            <aside class="payment-summary-card">
                <span class="section-kicker">Детали сеанса</span>
                <h2>{{ $movie->title }}</h2>
                <div class="payment-summary-list">
                    <p><strong>Город:</strong> {{ $city->name }}</p>
                    <p><strong>Дата:</strong> {{ \Carbon\Carbon::parse($showDate)->format('d.m.Y') }}</p>
                    <p><strong>Время:</strong> {{ $showTime }}</p>
                    <p><strong>Площадка:</strong> {{ $movie->venue ?? 'Выездная площадка кинотеатра' }}</p>
                    <p><strong>Цена:</strong> {{ $ticketPrice }} ₽ / билет</p>
                    <p><strong>Осталось:</strong> {{ $availableTickets }} из {{ $maxTickets }}</p>
                </div>
                <div class="payment-total-box">
                    <span>Итого</span>
                    <strong data-payment-total>{{ old('quantity', 1) * $ticketPrice }} ₽</strong>
                </div>
            </aside>

            <div class="payment-form-card">
                <div class="demo-payment-callout">
                    <strong>Демо-оплата</strong>
                    <p>Используйте тестовый номер 4242 4242 4242 4242, любой будущий срок действия и CVV из 3 цифр. Реальные платёжные шлюзы не подключены.</p>
                </div>

                @if ($availableTickets <= 0)
                    <div class="empty-state compact-empty">
                        <div class="empty-state-icon">🚫</div>
                        <h4>Билеты распроданы</h4>
                        <p>На выбранный сеанс мест не осталось. Вернитесь в афишу и выберите другой показ.</p>
                        <a href="{{ route('afisha.index') }}" class="btn btn-main btn-sm">Открыть афишу</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('tickets.store') }}" class="cinema-form payment-form" novalidate>
                        @csrf
                        <input type="hidden" name="city_id" value="{{ $city->id }}">
                        <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                        <input type="hidden" name="show_date" value="{{ $showDate }}">
                        <input type="hidden" name="show_time" value="{{ $showTime }}">

                        <div class="form-group">
                            <label class="form-label" for="quantity">Количество билетов</label>
                            <input id="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" min="1" max="{{ $availableTickets }}" value="{{ old('quantity', 1) }}" required data-ticket-quantity data-unit-price="{{ $ticketPrice }}" data-total-target="[data-payment-total]">
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="card_number">Номер карты</label>
                            <input id="card_number" type="text" inputmode="numeric" autocomplete="cc-number" class="form-control @error('card_number') is-invalid @enderror" name="card_number" value="{{ old('card_number') }}" placeholder="4242 4242 4242 4242" required data-card-number>
                            @error('card_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="card_holder">Держатель карты</label>
                            <input id="card_holder" type="text" autocomplete="cc-name" class="form-control @error('card_holder') is-invalid @enderror" name="card_holder" value="{{ old('card_holder') }}" placeholder="IVAN IVANOV" required>
                            @error('card_holder')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="payment-card-row">
                            <div class="form-group">
                                <label class="form-label" for="card_expiry">Срок действия</label>
                                <input id="card_expiry" type="text" inputmode="numeric" autocomplete="cc-exp" class="form-control @error('card_expiry') is-invalid @enderror" name="card_expiry" value="{{ old('card_expiry') }}" placeholder="MM/YY" required data-card-expiry>
                                @error('card_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="card_cvv">CVV</label>
                                <input id="card_cvv" type="password" inputmode="numeric" autocomplete="cc-csc" class="form-control @error('card_cvv') is-invalid @enderror" name="card_cvv" placeholder="123" required data-card-cvv>
                                @error('card_cvv')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success payment-submit">Оплатить демо-билет</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
