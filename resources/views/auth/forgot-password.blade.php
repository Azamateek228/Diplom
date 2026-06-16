@extends('layouts.app')

@section('content')
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header auth-header-warning">
                <h4>Забыли пароль?</h4>
            </div>
            <div class="auth-body">
                @if (session('success'))
                    <div class="auth-alert auth-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <p class="auth-description">
                    Введите ваш email адрес, и мы отправим вам ссылку для сброса пароля.
                </p>

                <form method="POST" action="{{ route('password.email') }}">
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

                    <!-- Кнопка отправки -->
                    <button type="submit" class="auth-btn auth-btn-warning">Отправить ссылку</button>
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
@endsection
