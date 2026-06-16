@extends('layouts.app')

@section('content')
<div class="auth-page">
    <div class="auth-container auth-container-wide">
        <div class="auth-card">
            <div class="auth-header auth-header-success">
                <h4>Настройки двухфакторной аутентификации</h4>
            </div>
            <div class="auth-body">
                @if (session('success'))
                    <div class="auth-alert auth-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (auth()->user()->two_factor_enabled)
                    <!-- 2FA включена -->
                    <div class="auth-alert auth-alert-success">
                        <strong>✓ Двухфакторная аутентификация включена</strong>
                        <p class="mb-0 mt-2">
                            При каждом входе вам потребуется вводить код из email.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Вы уверены, что хотите отключить двухфакторную аутентификацию? Это снизит безопасность вашего аккаунта.')">
                        @csrf
                        
                        <div class="auth-form-group">
                            <label for="password" class="auth-label">Подтвердите пароль</label>
                            <input 
                                type="password" 
                                class="auth-input @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password"
                                placeholder="Введите пароль для подтверждения"
                                required
                            >
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="auth-btn auth-btn-danger">Отключить 2FA</button>
                    </form>
                @else
                    <!-- 2FA выключена -->
                    <div class="auth-alert auth-alert-warning">
                        <strong>⚠ Двухфакторная аутентификация отключена</strong>
                        <p class="mb-0 mt-2">
                            Рекомендуется включить для дополнительной защиты аккаунта.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <button type="submit" class="auth-btn auth-btn-success">Включить 2FA</button>
                    </form>
                @endif

                <div class="auth-divider">
                    <span>или</span>
                </div>

                <div class="auth-footer">
                    <a href="{{ route('profile.edit') }}" class="auth-link">← Вернуться к профилю</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
