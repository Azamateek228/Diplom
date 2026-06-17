@extends('layouts.app')

@section('content')
<div class="profile-page">
    <h2 class="profile-title">👤 Профиль</h2>

    @if (session('success'))
        <div class="auth-alert auth-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="auth-alert auth-alert-warning">
            {{ session('error') }}
        </div>
    @endif

    <div class="profile-layout">
        <aside class="profile-sidebar profile-card">
            <h5 class="profile-card-title">Меню</h5>
            <a href="{{ route('profile.edit', ['tab' => 'profile']) }}" class="profile-menu-link {{ $tab === 'profile' ? 'active' : '' }}">
                👤 Профиль
            </a>
            <a href="{{ route('profile.edit', ['tab' => 'tickets']) }}" class="profile-menu-link {{ $tab === 'tickets' ? 'active' : '' }}">
                🎟️ Мои билеты
            </a>
        </aside>

        <section class="profile-content">
            @if ($tab === 'profile')
                <div class="profile-grid">
                    <div class="profile-section">
                        <div class="profile-card">
                            <h5 class="profile-card-title">Основная информация</h5>
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('PUT')

                                <div class="auth-form-group">
                                    <label class="auth-label">Имя</label>
                                    <input type="text" class="auth-input" value="{{ $user->name }}" disabled>
                                </div>

                                <div class="auth-form-group">
                                    <label class="auth-label">Email</label>
                                    <input type="email" class="auth-input" value="{{ $user->email }}" disabled>
                                </div>

                                <div class="auth-form-group">
                                    <label class="auth-label">Мой город</label>
                                    <select name="city_id" class="auth-input">
                                        <option value="">— не выбран —</option>
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}" {{ $user->city_id == $city->id ? 'selected' : '' }}>
                                                {{ $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="auth-form-text">Ваш город используется для голосования. Если вы голосуете за фильм, голос будет учтён именно в выбранном городе.</small>
                                </div>

                                <button type="submit" class="auth-btn auth-btn-primary">Сохранить</button>
                            </form>
                        </div>
                    </div>

                </div>
            @endif

            @if ($tab === 'tickets')
                <div class="profile-card">
                    <h5 class="profile-card-title">Мои билеты и QR</h5>
                    @if (isset($tickets) && $tickets->count())
                        <div class="ticket-cards-grid">
                            @foreach ($tickets as $ticket)
                                <div class="ticket-card fade-in-up">
                                    <div class="ticket-card-top">
                                        <div>
                                            <p class="ticket-movie-title">{{ $ticket->movie->title ?? 'Фильм' }}</p>
                                            <p class="ticket-city">{{ $ticket->city->name ?? 'Город' }}</p>
                                        </div>
                                        <span class="ticket-status {{ $ticket->status === 'refunded' ? 'status-refunded' : 'status-paid' }}">
                                            {{ $ticket->status === 'refunded' ? 'Возвращён' : 'Оплачен' }}
                                        </span>
                                    </div>
                                    <div class="ticket-meta">
                                        <p class="mb-1">Дата: {{ $ticket->show_date?->format('d.m.Y') }} {{ $ticket->show_time }}</p>
                                        <p class="mb-1">Билетов: {{ $ticket->quantity }}</p>
                                        <p class="mb-0">Сумма: {{ $ticket->total_price }} ₽</p>
                                    </div>

                                    @if ($ticket->status === 'purchased')
                                        <div class="ticket-qr-wrap demo-qr-block">
                                            <div class="demo-qr-pattern" aria-label="Демо QR-код билета"></div>
                                            <p class="mb-1"><strong>Демо QR-код</strong></p>
                                            <p class="mb-0 small">Код билета: <code>{{ $ticket->qr_token }}</code></p>
                                        </div>
                                        <div class="ticket-refund-note mt-2">
                                            Возврат доступен до: {{ $ticket->refund_available_until->format('d.m.Y H:i') }}
                                        </div>
                                        @if ($ticket->can_refund)
                                            <form method="POST" action="{{ route('tickets.refund', $ticket) }}" class="mt-2">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Оформить возврат</button>
                                            </form>
                                        @else
                                            <p class="ticket-refund-note mt-2 mb-0">Возврат недоступен: до показа осталось менее 24 часов.</p>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">🎟️</div>
                            <h4>Билетов пока нет</h4>
                            <p>Выберите фильм в афише и оформите первую покупку.</p>
                            <a href="{{ route('afisha.index') }}" class="btn btn-main btn-sm">Перейти в афишу</a>
                        </div>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
