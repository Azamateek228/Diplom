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

                    <div class="profile-section">
                        <div class="profile-card">
                            <h5 class="profile-card-title">🔐 Безопасность</h5>

                            <div class="security-item">
                                <div class="security-item-info">
                                    <strong>Двухфакторная аутентификация</strong>
                                    <p class="security-item-status">
                                        {{ $user->two_factor_enabled ? 'Включена' : 'Отключена' }}
                                    </p>
                                </div>
                                <a href="{{ route('two-factor.settings') }}" class="auth-btn auth-btn-sm auth-btn-outline">
                                    Настроить
                                </a>
                            </div>

                            <div class="security-divider"></div>

                            <div class="security-item">
                                <div class="security-item-info">
                                    <strong>Сменить пароль</strong>
                                    <p class="security-item-status">
                                        Последний раз изменён {{ $user->updated_at->format('d.m.Y') }}
                                    </p>
                                </div>
                                <a href="{{ route('password.request') }}" class="auth-btn auth-btn-sm auth-btn-outline">
                                    Изменить
                                </a>
                            </div>

                            @if ($user->is_locked)
                                <div class="security-divider"></div>
                                <div class="auth-alert auth-alert-warning">
                                    <strong>⚠ Аккаунт заблокирован</strong>
                                    <p class="mb-0 small">
                                        Причина: {{ $user->lock_reason ?? 'Неизвестно' }}<br>
                                        Разблокировка: {{ $user->lock_expires_at?->format('d.m.Y H:i') ?? '—' }}
                                    </p>
                                </div>
                            @endif
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
                                        <div class="ticket-qr-wrap">
                                            <img
                                                src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($ticket->qr_token) }}"
                                                alt="QR Ticket"
                                                loading="lazy"
                                                onerror="this.style.display='none'; this.parentElement.querySelector('.qr-fallback').style.display='block';"
                                            >
                                            <div class="qr-fallback" style="display:none;">
                                                <p class="mb-1"><strong>QR временно недоступен</strong></p>
                                                <p class="mb-0 small">Код билета: <code>{{ $ticket->qr_token }}</code></p>
                                            </div>
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
