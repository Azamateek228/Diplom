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
                    На ваш email <strong>{{ session('2fa_user_email', auth()->user()->email ?? '') }}</strong> был отправлен 6-значный код подтверждения.
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const resendButton = document.getElementById('resendCode');
    const resendTimer = document.getElementById('resendTimer');
    const countdownElement = document.getElementById('countdown');
    
    // Разрешаем только цифры
    codeInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Автофокус на поле ввода
    codeInput.focus();
    
    // Повторная отправка кода
    resendButton.addEventListener('click', function() {
        resendCode();
    });
    
    function resendCode() {
        resendButton.disabled = true;
        resendButton.textContent = 'Отправка...';
        
        fetch("{{ route('two-factor.resend') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resendButton.textContent = 'Код отправлен!';
                resendButton.classList.remove('auth-btn-outline');
                resendButton.classList.add('auth-btn-success');
                startTimer();
            } else {
                resendButton.disabled = false;
                resendButton.textContent = 'Отправить код повторно';
                alert('Ошибка при отправке кода. Попробуйте позже.');
            }
        })
        .catch(error => {
            resendButton.disabled = false;
            resendButton.textContent = 'Отправить код повторно';
            alert('Ошибка при отправке кода. Попробуйте позже.');
        });
    }
    
    function startTimer() {
        let seconds = 60;
        resendButton.style.display = 'none';
        resendTimer.style.display = 'block';
        
        const timer = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(timer);
                resendTimer.style.display = 'none';
                resendButton.style.display = 'inline-block';
                resendButton.disabled = false;
                resendButton.textContent = 'Отправить код повторно';
                resendButton.classList.remove('auth-btn-success');
                resendButton.classList.add('auth-btn-outline');
            }
        }, 1000);
    }
});
</script>
@endsection
