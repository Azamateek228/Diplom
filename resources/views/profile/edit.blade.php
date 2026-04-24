@extends('layouts.app')

@section('content')
<div class="profile-page">
    <h2 class="profile-title">👤 Профиль</h2>

    @if (session('success'))
        <div class="auth-alert auth-alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="profile-grid">
        <div class="profile-section">
            <div class="profile-card">
                <h5 class="profile-card-title">Основная информация и оплата</h5>
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
                    </div>

                    <div class="auth-form-group">
                        <label class="auth-label">Форма оплаты</label>
                        <select name="payment_method" class="auth-input">
                            <option value="">— не выбрана —</option>
                            <option value="card_qr" {{ $user->payment_method === 'card_qr' ? 'selected' : '' }}>QR-код</option>
                            <option value="pdf_invoice" {{ $user->payment_method === 'pdf_invoice' ? 'selected' : '' }}>PDF-квитанция</option>
                        </select>
                    </div>

                    <div class="auth-form-group">
                        <label class="auth-label">Ссылка на QR-код</label>
                        <input type="url" name="payment_qr_url" class="auth-input"
                               value="{{ old('payment_qr_url', $user->payment_qr_url) }}"
                               placeholder="https://.../payment-qr.png">
                    </div>

                    <div class="auth-form-group">
                        <label class="auth-label">Ссылка на PDF</label>
                        <input type="url" name="payment_pdf_url" class="auth-input"
                               value="{{ old('payment_pdf_url', $user->payment_pdf_url) }}"
                               placeholder="https://.../invoice.pdf">
                    </div>

                    <button type="submit" class="auth-btn auth-btn-primary">Сохранить</button>
                </form>
            </div>
        </div>

        <div class="profile-section">
            <div class="profile-card">
                <h5 class="profile-card-title">🎟️ Мои билеты</h5>
                @forelse($tickets as $ticket)
                    <div class="security-item">
                        <div class="security-item-info">
                            <strong>{{ $ticket->movie->title }}</strong>
                            <p class="security-item-status">
                                {{ $ticket->city->name }} · {{ $ticket->quantity }} шт. · {{ $ticket->total_amount }} ₽
                                @if($ticket->refunded_at)
                                    <br><span class="text-success">Возвращён: {{ $ticket->refunded_at->format('d.m.Y H:i') }}</span>
                                @endif
                            </p>
                        </div>

                        @if(!$ticket->refunded_at)
                            <form method="POST" action="{{ route('tickets.refund', $ticket) }}">
                                @csrf
                                <button type="submit" class="auth-btn auth-btn-sm auth-btn-outline">Возврат</button>
                            </form>
                        @endif
                    </div>
                    <div class="security-divider"></div>
                @empty
                    <p class="text-muted mb-0">Пока нет покупок.</p>
                @endforelse
            </div>

            <div class="profile-card mt-3">
                <h5 class="profile-card-title">🔐 Безопасность</h5>
                <div class="security-item">
                    <div class="security-item-info">
                        <strong>Двухфакторная аутентификация</strong>
                        <p class="security-item-status">
                            {{ $user->two_factor_enabled ? 'Включена' : 'Отключена' }}
                        </p>
                    </div>
                    <a href="{{ route('two-factor.settings') }}" class="auth-btn auth-btn-sm auth-btn-outline">Настроить</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
