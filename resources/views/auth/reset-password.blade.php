@extends('layouts.app')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header auth-header-warning">
                <h4>Сброс пароля</h4>
            </div>
            <div class="auth-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <!-- Email -->
                    <div class="auth-form-group">
                        <label for="email" class="auth-label">Email</label>
                        <input 
                            type="email" 
                            class="auth-input @error('email') is-invalid @enderror" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            placeholder="example@mail.ru"
                            required
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Пароль -->
                    <div class="auth-form-group">
                        <label for="password" class="auth-label">Новый пароль</label>
                        <div class="auth-input-group">
                            <input 
                                type="password" 
                                class="auth-input @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password"
                                placeholder="Минимум 8 символов"
                                required
                                minlength="8"
                            >
                            <button 
                                class="auth-input-toggle" 
                                type="button" 
                                id="togglePassword"
                                aria-label="Показать пароль"
                            >
                                👁️
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="auth-form-text">
                            Пароль должен содержать заглавные и строчные буквы, цифры и специальные символы
                        </small>
                    </div>

                    <!-- Подтверждение пароля -->
                    <div class="auth-form-group">
                        <label for="password_confirmation" class="auth-label">Подтвердите пароль</label>
                        <input 
                            type="password" 
                            class="auth-input @error('password_confirmation') is-invalid @enderror" 
                            id="password_confirmation" 
                            name="password_confirmation"
                            placeholder="Повторите пароль"
                            required
                        >
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Кнопка сброса -->
                    <button type="submit" class="auth-btn auth-btn-warning">Сбросить пароль</button>
                </form>

                <div class="auth-divider">
                    <span>или</span>
                </div>

                <div class="auth-footer">
                    <p>
                        <a href="{{ route('login') }}" class="auth-link">← Вернуться ко входу</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
    });
});
</script>
@endsection
