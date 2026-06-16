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
                    Мы отправили 6-значный код на вашу почту <strong>{{ session('2fa_setup_email', auth()->user()->email) }}</strong>.
                    Код действует 5 минут.
                </div>

                <form method="POST" action="{{ route('two-factor.confirm') }}">
                    @csrf
                    <div class="auth-form-group">
                        <label for="code" class="auth-label">Код подтверждения</label>
                        <input type="text" class="auth-input @error('code') is-invalid @enderror" id="code" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" required autofocus>
                        @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="auth-btn auth-btn-success">Подтвердить и включить 2FA</button>
                </form>

                <div class="resend-section">
                    <p class="resend-text">Не пришло письмо?</p>
                    <button type="button" class="auth-btn auth-btn-sm auth-btn-outline" id="resendCode">Отправить код повторно</button>
                    <p class="resend-timer" id="resendTimer" style="display:none;">Отправим через <span id="countdown">60</span> сек.</p>
                </div>

                <div class="auth-divider"><span>или</span></div>
                <div class="auth-footer"><a href="{{ route('two-factor.settings') }}" class="auth-link">Отмена</a></div>
            </div>
        </div>
    </div>
</div>

@include('partials.two-factor-resend-script')
@endsection
