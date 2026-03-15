<footer class="bg-dark text-light py-4 mt-5" role="contentinfo">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5 class="fw-bold text-warning">🎬 Кино на колёсах</h5>
                <p class="text-muted small">
                    Уникальный выездной кинотеатр. Мы привозим кино туда, где нет кинотеатров.
                </p>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold">Навигация</h6>
                <ul class="list-unstyled small">
                    <li><a href="{{ route('movies.index') }}" class="text-muted text-decoration-none">🎬 Афиша</a></li>
                    <li><a href="{{ route('route.index') }}" class="text-muted text-decoration-none">🗓️ Маршрут</a></li>
                    <li><a href="/map" class="text-muted text-decoration-none">🗺️ Карта</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="fw-bold">Информация</h6>
                <ul class="list-unstyled small">
                    <li><a href="/sitemap.xml" class="text-muted text-decoration-none">📄 Карта сайта</a></li>
                    <li><a href="/robots.txt" class="text-muted text-decoration-none">🤖 Robots.txt</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="small text-muted mb-0">
                    &copy; {{ date('Y') }} Кинотеатр на колёсах. Все права защищены.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="small text-muted mb-0">
                    Сделано с ❤️ для любителей кино
                </p>
            </div>
        </div>
    </div>
</footer>
