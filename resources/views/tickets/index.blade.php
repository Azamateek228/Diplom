@extends('layouts.app')

@section('content')
    <section class="page-hero compact-hero">
        <span class="eyebrow">Личный кабинет зрителя</span>
        <h1>Мои билеты</h1>
        <p>Здесь собраны оплаченные и возвращённые билеты на показы выездного кинотеатра.</p>
    </section>

    @if ($tickets->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">🎟️</div>
            <h4>Билетов пока нет</h4>
            <p>Выберите ближайший сеанс в афише и оформите первую покупку.</p>
            <a href="{{ route('afisha.index') }}" class="btn btn-main btn-sm">Перейти в афишу</a>
        </div>
    @else
        <div class="ticket-cards-grid tickets-page-grid">
            @foreach ($tickets as $ticket)
                <article class="ticket-card fade-in-up">
                    <div class="ticket-card-top">
                        <div>
                            <p class="ticket-movie-title">{{ $ticket->movie->title ?? 'Фильм удалён' }}</p>
                            <p class="ticket-city">{{ $ticket->city->name ?? 'Город не указан' }}</p>
                        </div>
                        <span class="ticket-status {{ $ticket->status === 'refunded' ? 'status-refunded' : 'status-paid' }}">
                            {{ $ticket->status === 'refunded' ? 'Возвращён' : 'Оплачен' }}
                        </span>
                    </div>

                    <div class="ticket-meta ticket-meta-grid">
                        <p><strong>Дата:</strong> {{ $ticket->show_date?->format('d.m.Y') ?? '—' }}</p>
                        <p><strong>Время:</strong> {{ $ticket->show_time ?? '—' }}</p>
                        <p><strong>Количество:</strong> {{ $ticket->quantity }}</p>
                        <p><strong>Сумма:</strong> {{ $ticket->total_price }} ₽</p>
                        <p><strong>Статус:</strong> {{ $ticket->status === 'refunded' ? 'Возвращён' : 'Оплачен' }}</p>
                    </div>

                    <div class="ticket-qr-wrap demo-qr-block">
                        <div class="demo-qr-pattern" aria-label="Демо QR-код"></div>
                        <p class="mb-1"><strong>Демо QR-блок</strong></p>
                        <p class="mb-0 small">Код билета: <code>{{ $ticket->qr_token }}</code></p>
                    </div>

                    @if ($ticket->can_refund)
                        <form method="POST" action="{{ route('tickets.refund', $ticket) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Оформить возврат</button>
                        </form>
                    @elseif ($ticket->status === 'purchased')
                        <p class="ticket-refund-note mt-2 mb-0">Возврат недоступен: до показа осталось менее 24 часов.</p>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
@endsection
