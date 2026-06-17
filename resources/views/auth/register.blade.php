@extends('layouts.app')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h4>Регистрация</h4>
            </div>
            <div class="auth-body">
                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf
                    
                    <!-- Имя -->
                    <div class="auth-form-group">
                        <label for="name" class="auth-label">Имя <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="auth-input @error('name') is-invalid @enderror" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}"
                            placeholder="Введите ваше имя"
                            required
                            minlength="2"
                            maxlength="50"
                            pattern="[a-zA-Zа-яА-ЯёЁ\s\-]+"
                            title="Имя может содержать только буквы, пробелы и дефис"
                        >
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="auth-form-text">Только буквы, пробелы и дефис (2-50 символов)</small>
                    </div>

                    <!-- Email -->
                    <div class="auth-form-group">
                        <label for="email" class="auth-label">Email <span class="text-danger">*</span></label>
                        <input 
                            type="email" 
                            class="auth-input @error('email') is-invalid @enderror" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            placeholder="example@mail.ru"
                            required
                            maxlength="255"
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="auth-form-text">Введите действующий email адрес</small>
                    </div>

                    <!-- Пароль -->
                    <div class="auth-form-group">
                        <label for="password" class="auth-label">Пароль <span class="text-danger">*</span></label>
                        <div class="auth-input-group">
                            <input 
                                type="password" 
                                class="auth-input @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password"
                                placeholder="Минимум 8 символов"
                                required
                                minlength="8"
                                maxlength="255"
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
                        
                        <!-- Индикатор сложности пароля -->
                        <div class="password-requirements">
                            <small class="auth-form-text">Пароль должен содержать:</small>
                            <ul class="password-requirements-list">
                                <li id="req-length" class="text-danger">Минимум 8 символов</li>
                                <li id="req-uppercase" class="text-danger">Заглавную букву (A-Z, А-Я)</li>
                                <li id="req-lowercase" class="text-danger">Строчную букву (a-z, а-я)</li>
                                <li id="req-number" class="text-danger">Цифру (0-9)</li>
                                <li id="req-special" class="text-danger">Специальный символ (!@#$%^&*...)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Подтверждение пароля -->
                    <div class="auth-form-group">
                        <label for="password_confirmation" class="auth-label">Подтвердите пароль <span class="text-danger">*</span></label>
                        <div class="auth-input-group">
                            <input 
                                type="password" 
                                class="auth-input @error('password_confirmation') is-invalid @enderror" 
                                id="password_confirmation" 
                                name="password_confirmation"
                                placeholder="Повторите пароль"
                                required
                            >
                            <button 
                                class="auth-input-toggle" 
                                type="button" 
                                id="togglePasswordConfirmation"
                                aria-label="Показать пароль"
                            >
                                👁️
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="auth-form-text">Введите пароль ещё раз для подтверждения</small>
                    </div>

                    <!-- Чекбокс принятия условий -->
                    <div class="auth-form-group auth-checkbox-group">
                        <input 
                            type="checkbox" 
                            class="auth-checkbox @error('accept_terms') is-invalid @enderror" 
                            id="accept_terms" 
                            name="accept_terms"
                            required
                        >
                        <label class="auth-checkbox-label" for="accept_terms">
                            Я принимаю <a href="{{ route('terms') }}" class="auth-link" target="_blank" rel="noopener">условия пользовательского соглашения</a>
                            <span class="text-danger">*</span>
                        </label>
                        @error('accept_terms')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Кнопка регистрации -->
                    <button type="submit" class="auth-btn auth-btn-primary">Зарегистрироваться</button>
                </form>

                <div class="auth-divider">
                    <span>или</span>
                </div>

                <div class="auth-footer">
                    <p>Уже есть аккаунт? <a href="{{ route('login') }}" class="auth-link">Войти</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    
    // Элементы требований
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    // Переключение видимости пароля
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.setAttribute('aria-label', type === 'password' ? 'Показать пароль' : 'Скрыть пароль');
    });

    togglePasswordConfirmation.addEventListener('click', function() {
        const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmationInput.setAttribute('type', type);
        this.setAttribute('aria-label', type === 'password' ? 'Показать пароль' : 'Скрыть пароль');
    });

    // Проверка требований к паролю в реальном времени
    passwordInput.addEventListener('input', function() {
        const value = this.value;
        
        // Длина
        checkRequirement(reqLength, value.length >= 8);
        
        // Заглавные буквы
        checkRequirement(reqUppercase, /[A-ZА-ЯЁ]/.test(value));
        
        // Строчные буквы
        checkRequirement(reqLowercase, /[a-zа-яё]/.test(value));
        
        // Цифры
        checkRequirement(reqNumber, /[0-9]/.test(value));
        
        // Специальные символы
        checkRequirement(reqSpecial, /[!@#$%^&*(),.?":{}|<>_\-\+=\[\]\\;'"\/`~]/.test(value));
    });

    function checkRequirement(element, isMet) {
        if (isMet) {
            element.classList.remove('text-danger');
            element.classList.add('text-success');
            element.innerHTML = '✓ ' + element.textContent.replace('✓ ', '');
        } else {
            element.classList.remove('text-success');
            element.classList.add('text-danger');
            element.innerHTML = element.textContent.replace('✓ ', '');
        }
    }

    // Валидация совпадения паролей
    passwordConfirmationInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            this.setCustomValidity('Пароли не совпадают');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
@endsection
