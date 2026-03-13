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
                        <small class="auth-form-text">Нужен для отображения статуса вашего города и подстановки в голосование.</small>
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
</div>
@endsection
