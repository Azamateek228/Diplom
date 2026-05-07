@extends('layouts.app')

@section('content')
    <div class="map-page">
        <h2 class="mb-4 text-center">Маршрут кинотеатра</h2>

        @if ($currentCity)
            <div class="current-city-info mb-3">
                <div class="alert alert-info">
                    📍 <strong>Текущее местоположение:</strong> {{ $currentCity->name }}
                </div>
            </div>
        @endif

        <div id="map"
            style="width: 100%; height: 600px; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.3); background: #1c1c2b; display: flex; align-items: center; justify-content: center;">
            <div style="color: #888; text-align: center;">
                <p>Загрузка карты...</p>
            </div>
        </div>

        @if (isset($citiesData) && count($citiesData) > 1)
            <div class="map-controls mt-3">
                <div id="routeInfo" class="route-info">
                    <div><strong>Старт:</strong> <span id="startCityName"></span></div>
                    <div><strong>Следующий город:</strong> <span id="nextCityName"></span></div>
                    <div class="progress-bar-container">
                        <div id="routeProgress" class="progress-bar"></div>
                    </div>
                </div>
                @auth
                    @if (auth()->user()->role === 'admin')
                        <div class="admin-controls mt-2">
                            <button id="stopAnimation" class="btn btn-secondary btn-sm">⏸Остановить</button>
                            <button id="resetAnimation" class="btn btn-warning btn-sm">Сбросить</button>
                        </div>
                    @endif
                @endauth
            </div>
        @elseif(isset($citiesData) && count($citiesData) === 1)
            <div class="alert alert-info mt-3 text-center">
                <p>Добавлен 1 город. Для отображения маршрута необходимо минимум 2 города с координатами.</p>
            </div>
        @else
            <div class="alert alert-warning mt-3 text-center">
                <p>Для отображения карты необходимо добавить города с координатами (широта и долгота)</p>
                <p><small>Добавьте города с координатами, чтобы построить маршрут</small></p>
            </div>
        @endif
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        let map;
        let routePolyline;
        let movingMarker;
        let cityMarkers = [];
        let isAnimating = false;
        let roadPathCoordinates = [];
        let animationFrameId = null;

        const cities = @json($citiesData ?? []);
        const currentCityId = @json($currentCity?->id ?? null);
        const defaultCenter = @json($defaultCenter ?? [55.7558, 37.6173]);

        function parseCoordinate(value) {
            if (typeof value === 'number') return value;
            if (typeof value === 'string') return parseFloat(value.replace(',', '.').trim());
            return NaN;
        }

        function hasValidCoordinates(city) {
            const lat = parseCoordinate(city.lat);
            const lng = parseCoordinate(city.lng);
            return !isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
        }

        function initMap() {
            const mapContainer = document.getElementById('map');
            mapContainer.innerHTML = '';

            map = L.map('map').setView(defaultCenter, cities.length > 0 ? 6 : 4);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const orderedCities = cities.filter(hasValidCoordinates);
            if (orderedCities.length === 0) {
                L.marker(defaultCenter).addTo(map).bindPopup('Добавьте города с координатами для отображения маршрута');
                return;
            }

            orderedCities.forEach((city) => {
                const lat = parseCoordinate(city.lat);
                const lng = parseCoordinate(city.lng);
                const isCurrent = currentCityId === city.id;

                const marker = L.circleMarker([lat, lng], {
                    radius: 8,
                    color: isCurrent ? '#ff0000' : '#1e98ff',
                    fillColor: isCurrent ? '#ff0000' : '#1e98ff',
                    fillOpacity: 0.9
                }).addTo(map).bindPopup(`
                    <div style="padding: 8px;">
                        <h4>${city.name}</h4>
                        <p>🗳️ Голосов: ${city.votes_count || 0}</p>
                        ${isCurrent ? '<p class="current-location-popup">📍 Текущее местоположение</p>' : ''}
                    </div>
                `);

                cityMarkers.push(marker);
            });

            const startCity = currentCityId
                ? orderedCities.find(c => c.id === currentCityId) || orderedCities[0]
                : orderedCities[0];

            movingMarker = L.marker([parseCoordinate(startCity.lat), parseCoordinate(startCity.lng)]).addTo(map)
                .bindPopup('<strong>Кинотеатр на колёсах</strong><br>Маркер маршрута');

            cities.length = 0;
            cities.push(...orderedCities);

            if (cities.length > 1) {
                buildRoute();
            }
        }

        async function buildRoute() {
            const points = cities.map(city => [parseCoordinate(city.lat), parseCoordinate(city.lng)]);
            roadPathCoordinates = await buildRoadPath(points);

            if (!roadPathCoordinates || roadPathCoordinates.length < 2) {
                return;
            }

            if (routePolyline) {
                map.removeLayer(routePolyline);
            }

            routePolyline = L.polyline(roadPathCoordinates, {
                color: '#ff8c00',
                weight: 6,
                opacity: 0.95
            }).addTo(map);

            map.fitBounds(routePolyline.getBounds(), { padding: [25, 25] });

            movingMarker.setLatLng(roadPathCoordinates[0]);

            if (!isAnimating) {
                animateVan();
            }
        }

        async function buildRoadPath(points) {
            const chunks = [];
            for (let i = 0; i < points.length - 1; i++) {
                const from = points[i];
                const to = points[i + 1];
                const segment = await buildRoadSegment(from, to);
                if (i > 0) segment.shift();
                chunks.push(...segment);
            }
            return chunks;
        }

        async function buildRoadSegment(fromPoint, toPoint) {
            const coordinates = `${fromPoint[1]},${fromPoint[0]};${toPoint[1]},${toPoint[0]}`;
            const response = await fetch(`https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson`);
            const data = await response.json();

            if (!data.routes || data.routes.length === 0) {
                throw new Error('Маршрут не построен');
            }

            return data.routes[0].geometry.coordinates.map(point => [point[1], point[0]]);
        }

        function animateVan() {
            if (isAnimating || !roadPathCoordinates.length) return;

            isAnimating = true;
            const routeInfo = document.getElementById('routeInfo');
            const startCityNameEl = document.getElementById('startCityName');
            const nextCityNameEl = document.getElementById('nextCityName');
            const progressBar = document.getElementById('routeProgress');

            if (routeInfo && startCityNameEl && nextCityNameEl) {
                startCityNameEl.textContent = cities[0]?.name || 'Маршрут не сформирован';
                nextCityNameEl.textContent = cities[1]?.name || 'Следующая остановка уточняется';
                routeInfo.style.display = 'block';
            }

            let pointIndex = 0;
            let lastTick = 0;
            const frameIntervalMs = 120;

            function animate(timestamp) {
                if (!isAnimating || !movingMarker) return;

                if (timestamp - lastTick >= frameIntervalMs) {
                    pointIndex = (pointIndex + 1) % roadPathCoordinates.length;
                    movingMarker.setLatLng(roadPathCoordinates[pointIndex]);
                    if (progressBar) progressBar.style.width = `${(pointIndex / roadPathCoordinates.length) * 100}%`;
                    lastTick = timestamp;
                }

                animationFrameId = requestAnimationFrame(animate);
            }

            animationFrameId = requestAnimationFrame(animate);
        }

        const stopBtn = document.getElementById('stopAnimation');
        const resetBtn = document.getElementById('resetAnimation');

        if (stopBtn) {
            stopBtn.addEventListener('click', function() {
                isAnimating = false;
                if (animationFrameId) cancelAnimationFrame(animationFrameId);
                const routeInfo = document.getElementById('routeInfo');
                if (routeInfo) routeInfo.style.display = 'none';
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                isAnimating = false;
                if (animationFrameId) cancelAnimationFrame(animationFrameId);
                const progressBar = document.getElementById('routeProgress');
                if (progressBar) progressBar.style.width = '0%';
                if (movingMarker && roadPathCoordinates.length > 0) movingMarker.setLatLng(roadPathCoordinates[0]);
                setTimeout(() => animateVan(), 500);
            });
        }

        initMap();
    </script>

@endsection
