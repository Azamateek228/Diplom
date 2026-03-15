@extends('layouts.app')

@section('content')
<div class="auth-page">
    <div class="auth-container auth-container-wide">
        <div class="auth-card">
            <div class="auth-header auth-header-success">
                <h4>Подтверждение включения 2FA</h4>
            </div>
            <div class="auth-body">
                <div class="auth-alert auth-alert-info">
                    <strong>✅ Код отправлен на ваш email</strong>
                    <p class="mb-0 mt-2">
                        Проверьте папку «Входящие» или «Спам».
                    </p>
                </div>

                <p class="auth-description">
                    Введите 6-значный код, который был отправлен на ваш email, чтобы подтвердить включение двухфакторной аутентификации.
                </p>

                <form method="POST" action="{{ route('two-factor.confirm') }}">
                    @csrf

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
                        <small class="auth-form-text">Код действителен в течение 5 минут</small>
                    </div>

                    <button type="submit" class="auth-btn auth-btn-success">Подтвердить и включить 2FA</button>
                </form>

                <div class="auth-divider">
                    <span>или</span>
                </div>

                <div class="auth-footer">
                    <a href="{{ route('profile.edit') }}" class="auth-link">Отмена</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');

    codeInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>
@endsection
