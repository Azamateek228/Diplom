@php
    // SEO данные по умолчанию
    $seoTitle = $seoTitle ?? 'Кинотеатр на колёсах — выездной кинотеатр в вашем городе';
    $seoDescription = $seoDescription ?? 'Уникальный выездной кинотеатр на колёсах. Голосуйте за фильмы, выбирайте маршрут следования. Показ фильмов под открытым небом в городах России.';
    $seoKeywords = $seoKeywords ?? 'кинотеатр на колёсах, выездной кинотеатр, мобильный кинотеатр, голосование за фильмы, показ фильмов, кино под открытым небом, афиша мероприятий';
    $seoImage = $seoImage ?? asset('images/banner1.png');
    $seoUrl = $seoUrl ?? url()->current();
    $canonical = $canonical ?? url()->current();
@endphp
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Основные SEO мета-теги -->
    <title>@yield('title', $seoTitle)</title>
    <meta name="title" content="@yield('title', $seoTitle)">
    <meta name="description" content="@yield('description', $seoDescription)">
    <meta name="keywords" content="@yield('keywords', $seoKeywords)">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $canonical }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:title" content="@yield('og:title', $seoTitle)">
    <meta property="og:description" content="@yield('og:description', $seoDescription)">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:site_name" content="Кинотеатр на колёсах">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $seoUrl }}">
    <meta property="twitter:title" content="@yield('twitter:title', $seoTitle)">
    <meta property="twitter:description" content="@yield('twitter:description', $seoDescription)">
    <meta property="twitter:image" content="{{ $seoImage }}">
    
    <!-- Robots -->
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="yandex" content="index, follow">
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    
    <!-- Preconnect для оптимизации загрузки -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://api-maps.yandex.ru">
    
    <!-- Стили -->
    @section('styles')
    <!-- Preload критических ресурсов -->
    <link rel="preload" href="{{ asset('css/style.min.css') }}" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" as="style">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Оптимизированный CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.min.css') }}">
    
    <!-- Шрифты -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @show
    
    <!-- Дополнительные стили для конкретных страниц -->
    @stack('styles')
    
    <!-- Скрипты аналитики (будут добавлены) -->
    @include('partials.analytics')
    
    <!-- Микроразметка Schema.org -->
    @include('partials.schema')
</head>

<body class="bg-light">
    @section('body')
    <!-- Навигация -->
    @include('layouts.navigation')
    
    <!-- Уведомления -->
    @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Основной контент -->
    <main id="main-content">
        @yield('content')
    </main>
    
    <!-- Футер -->
    @include('layouts.footer')
    @show
    
    <!-- Скрипты -->
    @section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Автозакрытие алертов через 5 секунд
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    @show
    
    <!-- Дополнительные скрипты для конкретных страниц -->
    @stack('scripts')
</body>

</html>
