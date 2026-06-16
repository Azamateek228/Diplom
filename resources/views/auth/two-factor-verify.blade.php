@extends('layouts.app')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header auth-header-info">
                <h4>Двухфакторная аутентификация</h4>
            </div>
            <div class="auth-body">
                <p class="auth-description">
                    Мы отправили 6-значный код на вашу почту <strong>{{ session('2fa_user_email', auth()->user()->email ?? '') }}</strong>.
                    Введите его для завершения входа.
                </p>

                <form method="POST" action="{{ route('two-factor.verify') }}">
                    @csrf
                    
                    <!-- Код -->
                    <div class="auth-form-group">
                        <label for="code" class="auth-label">Код подтверждения</label>
                        <input 
                            type="text" 
                            class="auth-input @error('code') is-invalid @enderror" 
                            id="code" 
                            name="code"
                            placeholder="000000"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            required
                            autofocus
                        >
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="auth-form-text">
                            Код действителен в течение 5 минут
                        </small>
                    </div>

                    <!-- Кнопка проверки -->
                    <button type="submit" class="auth-btn auth-btn-info">Проверить код</button>
                </form>

                <!-- Кнопка повторной отправки -->
                <div class="resend-section">
                    <p class="resend-text">Не пришло письмо?</p>
                    <button type="button" class="auth-btn auth-btn-sm auth-btn-outline" id="resendCode">
                        Отправить код повторно
                    </button>
                    <p class="resend-timer" id="resendTimer" style="display: none;">
                        Отправим через <span id="countdown">60</span> сек.
                    </p>
                </div>

                <div class="auth-divider">
                    <span>или</span>
                </div>

                <div class="auth-footer">
                    <p class="text-muted">
                        <a href="{{ route('login') }}" class="auth-link">← Вернуться ко входу</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.two-factor-resend-script')

@endsection
