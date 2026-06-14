@extends('layouts.app')

@section('content')
    <div class="map-page">
        <h2 class="mb-4 text-center">Маршрут кинотеатра</h2>

        <div class="current-city-info mb-3">
            <div class="alert alert-info">
                <div><strong>{{ $routeLabel ?? 'Тип маршрута: длинный, так как голосов пока нет' }}</strong></div>
                @if ($currentCity)
                    <div class="mt-1">📍 <strong>Текущее местоположение:</strong> {{ $currentCity->name }}</div>
                @endif
            </div>
        </div>

        <div class="map-toolbar mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div class="route-state-hint">
                Вид карты сохраняется в этом браузере. Текущая остановка берётся из настроек маршрута.
            </div>
            <button id="fitRouteButton" type="button" class="btn btn-main btn-sm">Показать весь маршрут</button>
        </div>

        <div id="map" class="map-shell">
            <div class="map-placeholder text-center">
                <p>Загрузка карты...</p>
            </div>
        </div>

        @if (isset($cities) && $cities->count())
            <div class="route-cities-list mt-4">
                <h3 class="mb-3">Список городов маршрута</h3>
                <ol class="list-group list-group-numbered">
                    @foreach ($cities as $city)
                        <li class="list-group-item d-flex justify-content-between align-items-center {{ $currentCity && (int) $currentCity->id === (int) $city->id ? 'active-route-city' : '' }}">
                            <span>{{ $city->name }}</span>
                            <span>
                                @if ($currentCity && (int) $currentCity->id === (int) $city->id)
                                    <span class="badge bg-primary rounded-pill">текущий город</span>
                                @endif
                                @if (($city->votes_count ?? 0) > 0)
                                    <span class="badge bg-success rounded-pill">{{ $city->votes_count }} голос(ов)</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        @if (isset($citiesData) && count($citiesData) > 1)
            <div class="map-controls mt-3">
                <div id="routeInfo" class="route-info">
                    <div><strong>🎬 Кинофургон:</strong> <span id="currentRouteCityName"></span></div>
                    <div><strong>Курс:</strong> <span id="nextCityName"></span></div>
                    <div><strong>Этап:</strong> <span id="routeLegName"></span></div>
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
                <p>Маршрут состоит из одного города. Это нормально для короткого маршрута, если голос есть только в одном городе.</p>
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
        let activeRoutePolyline;
        let movingMarker;
        let cityMarkers = [];
        let isAnimating = false;
        let roadPathCoordinates = [];
        let routeSegments = [];
        let animationFrameId = null;
        let restoredViewport = null;
        let savedRouteState = null;
        let isRestoringViewport = false;

        const cities = @json($citiesData ?? []);
        const currentCityId = @json($currentCity?->id ?? null);
        const defaultCenter = @json($defaultCenter ?? [55.7558, 37.6173]);
        const MAP_VIEW_STORAGE_KEY = 'cinemaRouteMap.viewport.v1';
        const ROUTE_STATE_STORAGE_KEY = 'cinemaRouteMap.routeState.v1';
        const routeSignature = cities.map(city => `${city.id}:${city.route_order || ''}`).join('|');

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


        function readStorageJson(key) {
            try {
                const raw = window.localStorage.getItem(key);
                return raw ? JSON.parse(raw) : null;
            } catch (error) {
                console.warn('Не удалось прочитать сохранённое состояние карты.', error);
                window.localStorage.removeItem(key);
                return null;
            }
        }

        function writeStorageJson(key, value) {
            try {
                window.localStorage.setItem(key, JSON.stringify(value));
            } catch (error) {
                console.warn('Не удалось сохранить состояние карты.', error);
            }
        }

        function getSavedViewport() {
            const viewport = readStorageJson(MAP_VIEW_STORAGE_KEY);
            if (!viewport || !Array.isArray(viewport.center) || viewport.center.length !== 2) {
                return null;
            }

            const lat = parseCoordinate(viewport.center[0]);
            const lng = parseCoordinate(viewport.center[1]);
            const zoom = Number(viewport.zoom);

            if (isNaN(lat) || isNaN(lng) || isNaN(zoom) || zoom < 1 || zoom > 19) {
                window.localStorage.removeItem(MAP_VIEW_STORAGE_KEY);
                return null;
            }

            return { center: [lat, lng], zoom };
        }

        function saveMapViewport() {
            if (!map || isRestoringViewport) return;

            const center = map.getCenter();
            writeStorageJson(MAP_VIEW_STORAGE_KEY, {
                center: [Number(center.lat.toFixed(6)), Number(center.lng.toFixed(6))],
                zoom: map.getZoom(),
                savedAt: Date.now(),
            });
        }

        function saveRouteState(pointIndex) {
            const segment = routeSegmentForPoint(pointIndex);
            writeStorageJson(ROUTE_STATE_STORAGE_KEY, {
                pointIndex,
                currentCityId,
                activeStopId: segment?.from?.id || currentCityId,
                routeSignature,
                savedAt: Date.now(),
            });
        }

        function getSavedRouteState() {
            const state = readStorageJson(ROUTE_STATE_STORAGE_KEY);
            if (!state || state.routeSignature !== routeSignature || Number(state.currentCityId) !== Number(currentCityId)) {
                return null;
            }

            const pointIndex = Number(state.pointIndex);
            if (isNaN(pointIndex) || pointIndex < 0) {
                return null;
            }

            return state;
        }

        function applyStoredViewport() {
            if (!map || !restoredViewport) return;

            isRestoringViewport = true;
            map.setView(restoredViewport.center, restoredViewport.zoom, { animate: false });
            setTimeout(() => { isRestoringViewport = false; }, 0);
        }

        function fitWholeRoute() {
            if (!map) return;

            if (routePolyline) {
                map.fitBounds(routePolyline.getBounds(), { padding: [32, 32] });
                saveMapViewport();
                return;
            }

            const orderedCities = cities.filter(hasValidCoordinates);
            if (orderedCities.length > 0) {
                const bounds = L.latLngBounds(orderedCities.map(city => [parseCoordinate(city.lat), parseCoordinate(city.lng)]));
                map.fitBounds(bounds, { padding: [32, 32] });
                saveMapViewport();
            }
        }

        function focusCurrentCityIfNoSavedViewport(orderedCities) {
            if (restoredViewport) return;

            const activeCity = currentCityId
                ? orderedCities.find(city => Number(city.id) === Number(currentCityId))
                : orderedCities[0];

            if (activeCity) {
                map.setView([parseCoordinate(activeCity.lat), parseCoordinate(activeCity.lng)], Math.max(map.getZoom(), 8), { animate: false });
            }
        }

        function initMap() {
            const mapContainer = document.getElementById('map');
            mapContainer.innerHTML = '';

            restoredViewport = getSavedViewport();
            savedRouteState = getSavedRouteState();
            const initialCenter = restoredViewport?.center || defaultCenter;
            const initialZoom = restoredViewport?.zoom || (cities.length > 0 ? 7 : 4);

            map = L.map('map').setView(initialCenter, initialZoom);
            map.on('moveend zoomend', saveMapViewport);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const orderedCities = cities.filter(hasValidCoordinates);
            if (orderedCities.length === 0) {
                L.marker(defaultCenter).addTo(map).bindPopup('Добавьте города с координатами для отображения маршрута');
                applyStoredViewport();
                return;
            }

            orderedCities.forEach((city) => {
                const lat = parseCoordinate(city.lat);
                const lng = parseCoordinate(city.lng);
                const isCurrent = Number(currentCityId) === Number(city.id);

                const marker = L.circleMarker([lat, lng], {
                    radius: isCurrent ? 11 : 8,
                    color: isCurrent ? '#f5b301' : '#1e98ff',
                    weight: isCurrent ? 4 : 2,
                    fillColor: isCurrent ? '#f97316' : '#1e98ff',
                    fillOpacity: 0.9
                }).addTo(map).bindPopup(`
                    <div class="map-popup-content">
                        <h4>${city.name}</h4>
                        <p>🗳️ Голосов: ${city.votes_count || 0}</p>
                        ${isCurrent ? '<p class="current-location-popup">📍 Текущее местоположение</p>' : ''}
                    </div>
                `);

                cityMarkers.push({ cityId: city.id, marker });
            });

            const startCity = currentCityId
                ? orderedCities.find(c => Number(c.id) === Number(currentCityId)) || orderedCities[0]
                : orderedCities[0];

            movingMarker = L.marker([parseCoordinate(startCity.lat), parseCoordinate(startCity.lng)]).addTo(map)
                .bindPopup('<strong>Кинотеатр на колёсах</strong><br>Маркер маршрута');

            cities.length = 0;
            cities.push(...orderedCities);
            focusCurrentCityIfNoSavedViewport(orderedCities);

            if (cities.length > 1) {
                buildRoute();
            } else {
                applyStoredViewport();
            }
        }

        async function buildRoute() {
            const route = await buildRoadPath(cities);
            roadPathCoordinates = route.coordinates;
            routeSegments = route.segments;

            if (!roadPathCoordinates || roadPathCoordinates.length < 2) {
                return;
            }

            if (routePolyline) {
                map.removeLayer(routePolyline);
            }

            routePolyline = L.polyline(roadPathCoordinates, {
                color: '#ff8c00',
                weight: 5,
                opacity: 0.45
            }).addTo(map);

            const startPointIndex = restoredAnimationPointIndex();
            movingMarker.setLatLng(roadPathCoordinates[startPointIndex]);
            updateRouteNavigator(startPointIndex);
            saveRouteState(startPointIndex);
            applyStoredViewport();

            if (!isAnimating) {
                animateVan(startPointIndex);
            }
        }

        async function buildRoadPath(routeCities) {
            const chunks = [];
            const segments = [];

            for (let i = 0; i < routeCities.length - 1; i++) {
                const fromCity = routeCities[i];
                const toCity = routeCities[i + 1];
                const from = [parseCoordinate(fromCity.lat), parseCoordinate(fromCity.lng)];
                const to = [parseCoordinate(toCity.lat), parseCoordinate(toCity.lng)];
                const segment = await buildRoadSegment(from, to);
                if (i > 0) segment.shift();

                const startIndex = chunks.length;
                chunks.push(...segment);
                const endIndex = Math.max(startIndex, chunks.length - 1);

                segments.push({
                    from: fromCity,
                    to: toCity,
                    startIndex,
                    endIndex,
                    legNumber: i + 1,
                    totalLegs: routeCities.length - 1,
                });
            }

            return { coordinates: chunks, segments };
        }

        async function buildRoadSegment(fromPoint, toPoint) {
            const coordinates = `${fromPoint[1]},${fromPoint[0]};${toPoint[1]},${toPoint[0]}`;
            try {
                const response = await fetch(`https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson`);
                const data = await response.json();

                if (!data.routes || data.routes.length === 0) {
                    throw new Error('Маршрут не построен');
                }

                return data.routes[0].geometry.coordinates.map(point => [point[1], point[0]]);
            } catch (error) {
                console.warn('OSRM недоступен, строим прямой отрезок между городами.', error);
                return [fromPoint, toPoint];
            }
        }

        function routeStartPointIndex() {
            if (!currentCityId || routeSegments.length === 0) {
                return 0;
            }

            const fromSegment = routeSegments.find(segment => Number(segment.from.id) === Number(currentCityId));
            if (fromSegment) {
                return fromSegment.startIndex;
            }

            const toSegment = routeSegments.find(segment => Number(segment.to.id) === Number(currentCityId));
            return toSegment ? toSegment.endIndex : 0;
        }

        function routeSegmentForPoint(pointIndex) {
            return routeSegments.find(segment => pointIndex >= segment.startIndex && pointIndex <= segment.endIndex)
                || routeSegments[routeSegments.length - 1]
                || null;
        }

        function restoredAnimationPointIndex() {
            const startPointIndex = routeStartPointIndex();
            if (!savedRouteState || !roadPathCoordinates.length) {
                return startPointIndex;
            }

            const pointIndex = Math.min(Number(savedRouteState.pointIndex), roadPathCoordinates.length - 1);
            return pointIndex >= 0 ? pointIndex : startPointIndex;
        }

        function refreshActiveRouteSegment(pointIndex) {
            const segment = routeSegmentForPoint(pointIndex);
            if (!segment || !roadPathCoordinates.length) return;

            if (activeRoutePolyline) {
                map.removeLayer(activeRoutePolyline);
            }

            const segmentCoordinates = roadPathCoordinates.slice(segment.startIndex, segment.endIndex + 1);
            if (segmentCoordinates.length > 1) {
                activeRoutePolyline = L.polyline(segmentCoordinates, {
                    color: '#f5b301',
                    weight: 7,
                    opacity: 0.95
                }).addTo(map);
            }
        }

        function updateRouteNavigator(pointIndex) {
            const routeInfo = document.getElementById('routeInfo');
            const currentRouteCityNameEl = document.getElementById('currentRouteCityName');
            const nextCityNameEl = document.getElementById('nextCityName');
            const routeLegNameEl = document.getElementById('routeLegName');
            const segment = routeSegmentForPoint(pointIndex);

            if (!routeInfo || !currentRouteCityNameEl || !nextCityNameEl || !routeLegNameEl || !segment) {
                return;
            }

            currentRouteCityNameEl.textContent = segment.from.name;
            nextCityNameEl.textContent = segment.to.name;
            routeLegNameEl.textContent = `${segment.legNumber} из ${segment.totalLegs}`;
            routeInfo.style.display = 'block';
            refreshActiveRouteSegment(pointIndex);

            if (movingMarker) {
                movingMarker.setPopupContent(`<strong>Кинофургон на маршруте</strong><br>${segment.from.name} → ${segment.to.name}`);
            }
        }

        function animateVan(initialPointIndex = 0) {
            if (isAnimating || !roadPathCoordinates.length) return;

            isAnimating = true;
            const progressBar = document.getElementById('routeProgress');
            let pointIndex = initialPointIndex;
            let lastTick = 0;
            const frameIntervalMs = 120;

            updateRouteNavigator(pointIndex);

            function animate(timestamp) {
                if (!isAnimating || !movingMarker) return;

                if (timestamp - lastTick >= frameIntervalMs) {
                    pointIndex = (pointIndex + 1) % roadPathCoordinates.length;
                    movingMarker.setLatLng(roadPathCoordinates[pointIndex]);
                    updateRouteNavigator(pointIndex);
                    saveRouteState(pointIndex);
                    if (progressBar) progressBar.style.width = `${(pointIndex / roadPathCoordinates.length) * 100}%`;
                    lastTick = timestamp;
                }

                animationFrameId = requestAnimationFrame(animate);
            }

            animationFrameId = requestAnimationFrame(animate);
        }

        const stopBtn = document.getElementById('stopAnimation');
        const resetBtn = document.getElementById('resetAnimation');
        const fitRouteBtn = document.getElementById('fitRouteButton');

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
                const startPointIndex = routeStartPointIndex();
                if (progressBar) progressBar.style.width = `${(startPointIndex / roadPathCoordinates.length) * 100}%`;
                if (movingMarker && roadPathCoordinates.length > 0) movingMarker.setLatLng(roadPathCoordinates[startPointIndex]);
                updateRouteNavigator(startPointIndex);
                saveRouteState(startPointIndex);
                setTimeout(() => animateVan(startPointIndex), 500);
            });
        }

        if (fitRouteBtn) {
            fitRouteBtn.addEventListener('click', fitWholeRoute);
        }

        initMap();
    </script>

@endsection
