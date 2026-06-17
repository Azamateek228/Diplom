@extends('layouts.app')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h4>Вход в систему</h4>
            </div>
            <div class="auth-body">
                @if (session('success'))
                    <div class="auth-alert auth-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
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
                        <label for="password" class="auth-label">Пароль</label>
                        <input 
                            type="password" 
                            class="auth-input @error('password') is-invalid @enderror" 
                            id="password" 
                            name="password"
                            placeholder="Введите пароль"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Запомнить меня -->
                    <div class="auth-form-group auth-checkbox-group">
                        <input 
                            type="checkbox" 
                            class="auth-checkbox" 
                            id="remember" 
                            name="remember"
                        >
                        <label class="auth-checkbox-label" for="remember">
                            Запомнить меня
                        </label>
                    </div>

                    <!-- Кнопка входа -->
                    <button type="submit" class="auth-btn auth-btn-primary">Войти</button>
                </form>

                <div class="auth-divider">
                    <span>или</span>
                </div>

                <div class="auth-footer">
                    <p>Нет аккаунта? <a href="{{ route('register') }}" class="auth-link">Зарегистрироваться</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
