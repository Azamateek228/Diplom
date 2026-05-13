@extends('layouts.app')

@section('content')
    <section class="demo-hero">
        <div class="demo-hero__content">
            <span class="eyebrow">Выездной кинотеатр по Татарстану</span>
            <h1>Кинотеатр на колёсах</h1>
            <p>
                Мобильный кинофургон приезжает в города, где не хватает современных кинопоказов: жители голосуют за фильм,
                маршрут складывается по городам тура, а зрители покупают билеты на ближайший сеанс.
            </p>
            <div class="hero-actions">
                <a href="{{ route('movies.index') }}" class="btn btn-main btn-lg">Смотреть афишу</a>
                <a href="{{ url('/map') }}" class="btn btn-outline-light btn-lg">Открыть карту</a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-success btn-lg">Войти</a>
                @else
                    <a href="{{ route('profile.edit') }}" class="btn btn-success btn-lg">Мой профиль</a>
                @endguest
            </div>
        </div>
        <div class="demo-hero__card">
            <img src="{{ asset('images/furgon.png') }}" alt="Кинофургон" class="hero-van">
            <div class="route-ticket">
                <strong>Сегодня в маршруте</strong>
                <span>{{ $routeCities->first()?->name ?? 'Татарстан' }}</span>
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="section-heading">
            <span class="eyebrow">Простой сценарий для зрителя</span>
            <h2>Как это работает</h2>
        </div>
        <div class="steps-grid">
            <div class="step-card"><span>1</span><h3>Выберите город</h3><p>Система показывает ближайшие точки маршрута и площадки будущих показов.</p></div>
            <div class="step-card"><span>2</span><h3>Голосуйте за фильм</h3><p>Каждый голос помогает определить победителя для конкретного города.</p></div>
            <div class="step-card"><span>3</span><h3>Кинофургон приезжает</h3><p>По результатам голосования формируется понятная афиша и порядок выезда.</p></div>
            <div class="step-card"><span>4</span><h3>Покупайте билет</h3><p>После формирования афиши можно оплатить билет и сохранить его в профиле.</p></div>
        </div>
    </section>

    <section class="landing-section">
        <div class="section-heading section-heading--row">
            <div>
                <span class="eyebrow">Каталог</span>
                <h2>Популярные фильмы</h2>
            </div>
            <a href="{{ route('afisha.index') }}" class="btn btn-outline-dark btn-sm">Вся афиша</a>
        </div>
        @if($upcomingMovies->isEmpty())
            <div class="empty-state"><div class="empty-state-icon">🎬</div><h4>Фильмы появятся после заполнения базы</h4><p>Запустите сидер, чтобы увидеть демо-каталог.</p></div>
        @else
            <div class="landing-movies-grid">
                @foreach($upcomingMovies as $movie)
                    <article class="landing-movie-card">
                        @if($movie->poster)
                            <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
                        @else
                            <div class="poster-fallback"><span>🎬</span><strong>{{ $movie->title }}</strong></div>
                        @endif
                        <div>
                            <h3>{{ $movie->title }}</h3>
                            <p>{{ $movie->genre }} • {{ $movie->age_rating }}+ • {{ $movie->duration }} мин</p>
                            <p>🗳️ Голосов: {{ $movie->votes_count ?? 0 }}</p>
                            <p>Показы формируются в афише по маршруту голосования.</p>
                            <a href="{{ route('movies.show', $movie) }}" class="btn btn-main btn-sm">Подробнее</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="landing-section route-section">
        <div class="section-heading">
            <span class="eyebrow">Маршрут кинотеатра</span>
            <h2>Города тура по Татарстану</h2>
            <p>Если голосов нет, маршрут идёт по всем городам. Если голоса есть, маршрут строится только по активным городам.</p>
        </div>
        <div class="route-pills">
            @forelse($routeCities as $city)
                <span>{{ $city->name }}</span>
            @empty
                <span>Маршрут пока пуст</span>
            @endforelse
        </div>
        <a href="{{ url('/map') }}" class="btn btn-main mt-3">Посмотреть карту маршрута</a>
    </section>

   
@endsection
