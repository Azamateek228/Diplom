@extends('layouts.seo')

@php
    $seoTitle = 'Карта маршрута кинотеатра — где мы сейчас показываем кино';
    $seoDescription = 'Интерактивная карта маршрута выездного кинотеатра. Следите за нашим местоположением в реальном времени и узнавайте, когда кинотеатр приедет в ваш город.';
    $seoKeywords = 'карта кинотеатра, маршрут кинотеатра, где кинотеатр сейчас, выездной кинотеатр карта, мобильный кинотеатр';
@endphp

@section('title', $seoTitle)
@section('description', $seoDescription)
@section('keywords', $seoKeywords)

@section('styles')
@parent
<link rel="stylesheet" href="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/css">
@endsection

@section('content')
    <div class="map-page py-5" aria-labelledby="map-title">
        <div class="container">
            <header class="text-center mb-4">
                <h1 id="map-title" class="display-4 fw-bold">🗺️ Карта маршрута</h1>
                <p class="lead text-muted">Следите за передвижением кинотеатра в реальном времени</p>
                <p class="text-muted small">
                    Интерактивная карта показывает текущее местоположение кинотеатра и маршрут следования
                </p>
            </header>

            @if ($currentCity)
                <div class="current-city-info mb-3" role="status" aria-live="polite">
                    <div class="alert alert-info text-center">
                        <span class="fs-4" aria-hidden="true">📍</span>
                        <strong>Текущее местоположение:</strong> {{ $currentCity->name }}
                    </div>
                </div>
            @endif

            <section aria-label="Интерактивная карта" class="mb-3">
                <div id="map"
                    style="width: 100%; height: 600px; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.3); background: #1c1c2b; display: flex; align-items: center; justify-content: center;"
                    role="application"
                    aria-label="Интерактивная карта маршрута кинотеатра">
                    <div class="text-center text-muted p-4">
                        <div class="spinner-border text-warning mb-3" role="status">
                            <span class="visually-hidden">Загрузка карты...</span>
                        </div>
                        <p>Загрузка карты...</p>
                    </div>
                </div>
            </section>

            @if (isset($citiesData) && count($citiesData) > 1)
                <div class="map-controls" role="region" aria-label="Управление картой">
                    <div id="routeInfo" class="route-info alert alert-warning text-center">
                        <strong>Маршрут:</strong> <span id="currentCityName"></span> → <span id="nextCityName"></span>
                        <div class="progress mt-2" role="progressbar" aria-label="Прогресс маршрута">
                            <div id="routeProgress" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 style="width: 0%"></div>
                        </div>
                    </div>
                    @auth
                        @if (auth()->user()->role === 'admin')
                            <div class="admin-controls mt-2 text-center">
                                <button id="stopAnimation" class="btn btn-secondary btn-sm me-2" aria-label="Остановить анимацию">
                                    ⏸ Остановить
                                </button>
                                <button id="resetAnimation" class="btn btn-warning btn-sm" aria-label="Сбросить анимацию">
                                    🔄 Сбросить
                                </button>
                            </div>
                        @endif
                    @endauth
                </div>
            @elseif(isset($citiesData) && count($citiesData) === 1)
                <div class="alert alert-info mt-3 text-center" role="status">
                    <p>Добавлен 1 город. Для отображения маршрута необходимо минимум 2 города с координатами.</p>
                </div>
            @else
                <div class="alert alert-warning mt-3 text-center" role="alert">
                    <p><strong>Карта временно недоступна</strong></p>
                    <p>Для отображения карты необходимо добавить города с координатами (широта и долгота)</p>
                    <p class="small text-muted mb-0">Добавьте города через админ-панель с указанием координат</p>
                </div>
            @endif

            <div class="text-center mt-4">
                <a href="{{ route('route.index') }}" class="btn btn-outline-primary" title="Расписание показов">
                    🗓️ Расписание на неделю
                </a>
                <a href="{{ route('movies.index') }}" class="btn btn-outline-secondary ms-2" title="Афиша фильмов">
                    🎬 Афиша
                </a>
            </div>
        </div>
    </div>

    {{-- Микроразметка для карты --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Map",
        "name": "Карта маршрута кинотеатра на колёсах",
        "description": "Интерактивная карта маршрута выездного кинотеатра",
        "url": "{{ url('/map') }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Кинотеатр на колёсах",
            "url": "{{ url('/') }}"
        }
    }
    </script>
@endsection

@section('scripts')
@parent
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<script>
    let map;
    let routePolyline;
    let vanMarker;
    let cityMarkers = [];
    let animationInterval;
    let currentRouteIndex = 0;
    let isAnimating = false;

    const cities = @json($citiesData ?? []);
    const currentCityId = @json($currentCity?->id ?? null);
    const defaultCenter = @json($defaultCenter ?? [55.7558, 37.6173]);

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function orderCities(citiesArray) {
        if (citiesArray.length <= 1) return citiesArray;

        const validCities = citiesArray.filter(city => {
            const lat = parseFloat(city.lat);
            const lng = parseFloat(city.lng);
            return !isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
        });

        if (validCities.length === 0) return citiesArray;

        const kalugaIndex = validCities.findIndex(c => 
            c.name.toLowerCase().includes('калуг') || c.name.toLowerCase().includes('kaluga'));
        const vorkutaIndex = validCities.findIndex(c => 
            c.name.toLowerCase().includes('воркут') || c.name.toLowerCase().includes('vorkuta'));

        if (kalugaIndex !== -1 && vorkutaIndex !== -1) {
            const ordered = [...validCities];
            const kaluga = ordered[kalugaIndex];
            const vorkuta = ordered[vorkutaIndex];
            ordered.splice(Math.max(kalugaIndex, vorkutaIndex), 1);
            ordered.splice(Math.min(kalugaIndex, vorkutaIndex), 1);
            ordered.sort((a, b) => {
                const distA = calculateDistance(parseFloat(kaluga.lat), parseFloat(kaluga.lng), parseFloat(a.lat), parseFloat(a.lng));
                const distB = calculateDistance(parseFloat(kaluga.lat), parseFloat(kaluga.lng), parseFloat(b.lat), parseFloat(b.lng));
                return distA - distB;
            });
            return [kaluga, ...ordered, vorkuta];
        }

        const ordered = [];
        const remaining = [...validCities];
        let startIndex = currentCityId ? remaining.findIndex(c => c.id === currentCityId) : 0;
        if (startIndex === -1) startIndex = 0;
        
        let current = remaining.splice(startIndex, 1)[0];
        ordered.push(current);

        while (remaining.length > 0) {
            let nearest = 0, minDist = Infinity;
            remaining.forEach((city, idx) => {
                const dist = calculateDistance(parseFloat(current.lat), parseFloat(current.lng), parseFloat(city.lat), parseFloat(city.lng));
                if (dist < minDist) { minDist = dist; nearest = idx; }
            });
            current = remaining.splice(nearest, 1)[0];
            ordered.push(current);
        }
        return ordered;
    }

    function initMap() {
        if (typeof ymaps === 'undefined') {
            setTimeout(initMap, 100);
            return;
        }

        ymaps.ready(function() {
            try {
                document.getElementById('map').innerHTML = '';
                map = new ymaps.Map('map', {
                    center: defaultCenter,
                    zoom: cities.length > 0 ? 6 : 4,
                    controls: ['zoomControl', 'fullscreenControl', 'typeSelector']
                });

                if (cities.length === 0) {
                    new ymaps.Placemark(defaultCenter, {
                        balloonContent: 'Добавьте города с координатами для отображения маршрута'
                    }, { preset: 'islands#redIcon' }).addTo(map.geoObjects);
                    return;
                }

                cities.forEach(city => {
                    const lat = parseFloat(city.lat), lng = parseFloat(city.lng);
                    if (isNaN(lat) || isNaN(lng)) return;
                    
                    const marker = new ymaps.Placemark([lat, lng], {
                        balloonContent: `<div style="padding:10px;"><h4>${city.name}</h4><p>Голосов: ${city.votes_count || 0}</p>${currentCityId === city.id ? '<p style="color:#ffcc00;font-weight:bold;">📍 Текущее местоположение</p>' : ''}</div>`
                    }, {
                        preset: currentCityId === city.id ? 'islands#redDotIcon' : 'islands#blueCircleDotIcon'
                    });
                    cityMarkers.push(marker);
                    map.geoObjects.add(marker);
                });

                if (cities.length > 0) {
                    const ordered = orderCities(cities);
                    let startIndex = currentCityId ? ordered.findIndex(c => c.id === currentCityId) : 0;
                    if (startIndex === -1) startIndex = 0;
                    
                    const startPos = [parseFloat(ordered[startIndex].lat), parseFloat(ordered[startIndex].lng)];
                    const vanImageUrl = '{{ asset('images/furgon.png') }}';

                    vanMarker = new ymaps.Placemark(startPos, {
                        balloonContent: '<strong>Кинотеатр на колёсах</strong><br>Текущее местоположение'
                    }, {
                        iconLayout: 'default#image',
                        iconImageHref: vanImageUrl,
                        iconImageSize: [80, 80],
                        iconImageOffset: [-40, -40],
                        preset: 'islands#yellowAutoIcon'
                    });

                    const img = new Image();
                    img.onerror = () => {
                        vanMarker.options.set('preset', 'islands#yellowAutoIcon');
                        vanMarker.options.unset('iconLayout');
                    };
                    img.src = vanImageUrl;

                    map.geoObjects.add(vanMarker);
                    cities.length = 0;
                    cities.push(...ordered);
                    currentRouteIndex = startIndex;

                    if (cities.length > 1) {
                        buildRoute();
                        setTimeout(() => { if (vanMarker && !isAnimating) animateVan(); }, 1000);
                    }
                }
            } catch (e) {
                console.error('Ошибка карты:', e);
                document.getElementById('map').innerHTML = '<div class="p-4 text-center text-danger"><p>Ошибка загрузки карты</p></div>';
            }
        });
    }

    function buildRoute() {
        if (cities.length < 2) return;
        if (routePolyline) map.geoObjects.remove(routePolyline);

        const ordered = orderCities(cities);
        const points = ordered.map(c => [parseFloat(c.lat), parseFloat(c.lng)]).filter(p => p);
        if (points.length < 2) return;

        routePolyline = new ymaps.Polyline(points, {}, {
            strokeColor: '#ffcc00',
            strokeWidth: 4,
            strokeStyle: '5 5'
        });
        map.geoObjects.add(routePolyline);
        try { map.setBounds(routePolyline.geometry.getBounds(), { checkZoomRange: true, duration: 500 }); } catch(e) {}
    }

    function animateVan() {
        if (cities.length < 2 || isAnimating) return;
        isAnimating = true;
        if (currentRouteIndex >= cities.length) currentRouteIndex = 0;

        function moveToNext() {
            const nextIdx = currentRouteIndex >= cities.length - 1 ? 0 : currentRouteIndex + 1;
            const curr = cities[currentRouteIndex], next = cities[nextIdx];
            if (!curr || !next) { isAnimating = false; return; }

            const dist = calculateDistance(parseFloat(curr.lat), parseFloat(curr.lng), parseFloat(next.lat), parseFloat(next.lng));
            const duration = Math.max(20000, (dist / 100) * 30000);

            animateBetween([parseFloat(curr.lat), parseFloat(curr.lng)], [parseFloat(next.lat), parseFloat(next.lng)], duration, nextIdx, () => {
                currentRouteIndex = nextIdx;
                if (isAnimating) setTimeout(moveToNext, 6000);
            });
        }
        moveToNext();
    }

    function animateBetween(start, end, duration, nextIdx, cb) {
        const startTime = Date.now();
        const [startLat, startLng] = start;
        const dLat = end[0] - startLat, dLng = end[1] - startLng;

        const routeInfo = document.getElementById('routeInfo');
        const currName = document.getElementById('currentCityName');
        const nextName = document.getElementById('nextCityName');
        const progress = document.getElementById('routeProgress');

        if (currName && nextName && cities[currentRouteIndex] && cities[nextIdx]) {
            currName.textContent = cities[currentRouteIndex].name;
            nextName.textContent = cities[nextIdx].name;
            if (routeInfo) routeInfo.style.display = 'block';
        }

        function step() {
            if (!vanMarker) { if (cb) cb(); return; }
            const elapsed = Date.now() - startTime;
            const p = Math.min(elapsed / duration, 1);
            const ease = p < 0.5 ? 4 * p * p * p : 1 - Math.pow(-2 * p + 2, 3) / 2;
            
            vanMarker.geometry.setCoordinates([startLat + dLat * ease, startLng + dLng * ease]);
            if (progress) progress.style.width = (p * 100) + '%';

            if (p < 1) requestAnimationFrame(step);
            else {
                if (progress) progress.style.width = '0%';
                if (cb) cb();
            }
        }
        step();
    }

    document.getElementById('stopAnimation')?.addEventListener('click', () => {
        isAnimating = false;
        const ri = document.getElementById('routeInfo');
        if (ri) ri.style.display = 'none';
    });

    document.getElementById('resetAnimation')?.addEventListener('click', () => {
        isAnimating = false;
        const ordered = orderCities(cities);
        cities.length = 0;
        cities.push(...ordered);
        if (cities.length > 1) buildRoute();
        if (cities.length > 0 && vanMarker) {
            vanMarker.geometry.setCoordinates([parseFloat(cities[0].lat), parseFloat(cities[0].lng)]);
        }
        currentRouteIndex = 0;
    });

    initMap();
</script>
@endsection

<style>
    .map-page { background: linear-gradient(135deg, #1c1c2b 0%, #2d2d44 100%); min-height: 100vh; }
    .route-info { background: rgba(255, 204, 0, 0.1); border: 1px solid rgba(255, 204, 0, 0.3); }
</style>
