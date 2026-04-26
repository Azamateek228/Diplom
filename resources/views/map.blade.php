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
                    <span id="currentCityName"></span> → <span id="nextCityName"></span>
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
                <p><small>Добавьте города через админ-панель с указанием координат</small></p>
            </div>
        @endif
    </div>

    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
    <script>
        let map;
        let routePolyline;
        let routeMultiRoute;
        let movingMarker;
        let cityMarkers = [];
        let isAnimating = false;
        let roadPathCoordinates = [];
        let animationFrameId = null;

        // Данные городов из PHP
        const cities = @json($citiesData ?? []);

        const currentCityId = @json($currentCity?->id ?? null);
        const defaultCenter = @json($defaultCenter ?? [55.7558, 37.6173]);

        console.log('Cities data:', cities);
        console.log('Default center:', defaultCenter);

        // Функция упорядочивания городов - универсальная логика
        function orderCities(citiesArray) {
            if (citiesArray.length <= 1) {
                return citiesArray;
            }

            // Фильтруем города с валидными координатами
            const validCities = citiesArray.filter(city => {
                const lat = parseFloat(city.lat);
                const lng = parseFloat(city.lng);
                return !isNaN(lat) && !isNaN(lng) && 
                       lat >= -90 && lat <= 90 && 
                       lng >= -180 && lng <= 180;
            });

            if (validCities.length === 0) {
                console.warn('Нет городов с валидными координатами');
                return citiesArray;
            }
            console.log('Упорядоченные города:', validCities.map(c => c.name));
            return validCities;
        }

        // Инициализация карты - ждем загрузки API
        function initMap() {
            if (typeof ymaps === 'undefined') {
                console.log('Ожидание загрузки Yandex Maps API...');
                setTimeout(initMap, 100);
                return;
            }

            ymaps.ready(function() {
                try {
                    // Очищаем контейнер карты
                    const mapContainer = document.getElementById('map');
                    mapContainer.innerHTML = '';

                    // Создаем карту
                    map = new ymaps.Map('map', {
                        center: defaultCenter,
                        zoom: cities.length > 0 ? 6 : 4,
                        controls: ['zoomControl', 'fullscreenControl', 'typeSelector']
                    });

                    console.log('Карта создана успешно');

                    if (cities.length === 0) {
                        // Если нет городов, показываем сообщение
                        const noCitiesMarker = new ymaps.Placemark(
                            defaultCenter, {
                                balloonContent: 'Добавьте города с координатами для отображения маршрута'
                            }, {
                                preset: 'islands#redIcon'
                            }
                        );
                        map.geoObjects.add(noCitiesMarker);
                        return;
                    }

                    // Создаем маркеры для городов
                    cities.forEach((city, index) => {
                        // Проверяем валидность координат
                        const lat = parseFloat(city.lat);
                        const lng = parseFloat(city.lng);
                        
                        if (isNaN(lat) || isNaN(lng)) {
                            console.warn(`Город "${city.name}" имеет невалидные координаты:`, city);
                            return;
                        }

                        const marker = new ymaps.Placemark(
                            [lat, lng], {
                                balloonContent: `
                                    <div style="padding: 10px;">
                                        <h4>${city.name}</h4>
                                        <p>🗳️ Голосов: ${city.votes_count || 0}</p>
                                        ${currentCityId === city.id ? '<p style="color: #ffcc00; font-weight: bold;">📍 Текущее местоположение</p>' : ''}
                                    </div>
                                `,
                                iconCaption: city.name
                            }, {
                                preset: currentCityId === city.id ? 'islands#redDotIcon' :
                                    'islands#blueCircleDotIcon',
                                iconColor: currentCityId === city.id ? '#ff0000' : '#1e98ff'
                            }
                        );

                        cityMarkers.push(marker);
                        map.geoObjects.add(marker);
                    });

                    // Создаем маркер фургончика
                    if (cities.length > 0) {
                        // Упорядочиваем города
                        const orderedCities = orderCities(cities);

                        // Начальная позиция: из админки (текущий город кинотеатра) или по умолчанию
                        let startPosition;

                        const adminCurrentCityIndex = currentCityId
                            ? orderedCities.findIndex(c => c.id === currentCityId)
                            : -1;

                        if (adminCurrentCityIndex !== -1) {
                            // В админке выбран текущий город — фургон стартует именно отсюда
                            const c = orderedCities[adminCurrentCityIndex];
                            startPosition = [parseFloat(c.lat), parseFloat(c.lng)];
                        } else if (orderedCities.length >= 2) {
                            // Иначе — по умолчанию: ближе к первому городу маршрута
                            const firstCity = orderedCities[0];
                            startPosition = [
                                parseFloat(firstCity.lat),
                                parseFloat(firstCity.lng)
                            ];
                        } else {
                            startPosition = [
                                parseFloat(orderedCities[0].lat),
                                parseFloat(orderedCities[0].lng)
                            ];
                        }

                        movingMarker = new ymaps.Placemark(
                            startPosition, {
                                balloonContent: '<strong>Кинотеатр на колёсах</strong><br>Маркер маршрута'
                            }, {
                                preset: 'islands#violetCircleDotIcon',
                                iconColor: '#7e57c2'
                            }
                        );
                        map.geoObjects.add(movingMarker);

                        // Обновляем массив cities для анимации (упорядоченный)
                        cities.length = 0;
                        cities.push(...orderedCities);

                    }

                    // Строим маршрут между городами
                    if (cities.length > 1) {
                        buildRoute();

                        const routeInfo = document.getElementById('routeInfo');
                        if (routeInfo) {
                            routeInfo.style.display = 'none';
                        }

                        // Автостарт движения маркера после построения карты.
                        setTimeout(function() {
                            if (movingMarker && !isAnimating) {
                                animateVan();
                            }
                        }, 1000);
                    }
                } catch (error) {
                    console.error('Ошибка при создании карты:', error);
                    document.getElementById('map').innerHTML =
                        '<div style="padding: 20px; text-align: center; color: #fff;"><p>Ошибка загрузки карты. Проверьте консоль браузера.</p><p style="font-size: 12px; color: #888;">' +
                        error.message + '</p></div>';
                }
            });
        }

        // Запускаем инициализацию карты
        initMap();

        // Функция построения маршрута по реальным дорогам.
        function buildRoute() {
            if (cities.length < 2) return;

            if (routePolyline) {
                map.geoObjects.remove(routePolyline);
                routePolyline = null;
            }

            if (routeMultiRoute) {
                map.geoObjects.remove(routeMultiRoute);
                routeMultiRoute = null;
            }

            const orderedCities = orderCities(cities);
            const routePoints = orderedCities
                .map(city => [parseFloat(city.lat), parseFloat(city.lng)])
                .filter(point => !isNaN(point[0]) && !isNaN(point[1]));

            if (routePoints.length < 2) {
                console.warn('Недостаточно точек для построения дорожного маршрута');
                return;
            }

            buildRoadPath(routePoints)
                .then((fullRoadPath) => {
                    if (!fullRoadPath || fullRoadPath.length < 2) {
                        roadPathCoordinates = [];
                        return;
                    }

                    roadPathCoordinates = fullRoadPath;

                    if (routePolyline) {
                        map.geoObjects.remove(routePolyline);
                    }

                    routePolyline = new ymaps.Polyline(
                        roadPathCoordinates, {}, {
                            strokeColor: '#ff8c00',
                            strokeWidth: 6,
                            strokeOpacity: 0.95
                        }
                    );

                    map.geoObjects.add(routePolyline);
                    map.setBounds(routePolyline.geometry.getBounds(), {
                        checkZoomRange: true,
                        duration: 400
                    });

                    if (movingMarker) {
                        movingMarker.geometry.setCoordinates(roadPathCoordinates[0]);
                    }

                    if (!isAnimating) {
                        animateVan();
                    }
                })
                .catch((error) => {
                    console.warn('Маршрут по дорогам не построен:', error);
                    roadPathCoordinates = routePoints;

                    routePolyline = new ymaps.Polyline(
                        roadPathCoordinates, {}, {
                            strokeColor: '#ff8c00',
                            strokeWidth: 5,
                            strokeOpacity: 0.9,
                            strokeStyle: 'shortdash'
                        }
                    );
                    map.geoObjects.add(routePolyline);
                });
        }

        function buildRoadPath(points) {
            return new Promise((resolve, reject) => {
                routeMultiRoute = new ymaps.multiRouter.MultiRoute({
                    referencePoints: points,
                    params: {
                        routingMode: 'auto',
                        results: 1
                    }
                }, {
                    wayPointVisible: false,
                    viaPointVisible: false,
                    routeActiveStrokeColor: '#ff8c00',
                    routeActiveStrokeWidth: 0,
                    routeActiveStrokeOpacity: 0,
                    boundsAutoApply: false
                });

                routeMultiRoute.model.events.once('requestsuccess', () => {
                    const activeRoute = routeMultiRoute.getActiveRoute();
                    if (!activeRoute) {
                        reject(new Error('Активный маршрут не найден'));
                        return;
                    }

                    const paths = activeRoute.getPaths();
                    let fullPath = [];

                    paths.each((path, idx) => {
                        const segmentCoords = path.geometry.getCoordinates();
                        if (!Array.isArray(segmentCoords) || segmentCoords.length === 0) {
                            return;
                        }
                        if (idx > 0) {
                            segmentCoords.shift();
                        }
                        fullPath = fullPath.concat(segmentCoords);
                    });

                    if (fullPath.length < 2) {
                        reject(new Error('Пустая геометрия дорожного маршрута'));
                        return;
                    }

                    resolve(fullPath);
                });

                routeMultiRoute.model.events.once('requestfail', (event) => {
                    reject(new Error('Ошибка запроса маршрута: ' + (event.get('error') || 'unknown')));
                });

                map.geoObjects.add(routeMultiRoute);
            });
        }

        // Функция анимации движения фургончика
        function animateVan() {
            if (cities.length < 2 || isAnimating) return;

            // Если дорожная геометрия еще не готова, пробуем позже.
            if (!roadPathCoordinates || roadPathCoordinates.length < 2) {
                setTimeout(animateVan, 1200);
                return;
            }

            isAnimating = true;
            animateAlongRoadPath(0);
        }

        // Движение по точкам дорожной геометрии маршрута.
        function animateAlongRoadPath(startIndex) {
            if (!isAnimating || !movingMarker || !roadPathCoordinates || roadPathCoordinates.length < 2) {
                isAnimating = false;
                return;
            }

            const speedPointsPerFrame = 1;
            const frameIntervalMs = 120;
            const routeInfo = document.getElementById('routeInfo');
            const currentCityNameEl = document.getElementById('currentCityName');
            const nextCityNameEl = document.getElementById('nextCityName');
            const progressBar = document.getElementById('routeProgress');

            if (routeInfo && currentCityNameEl && nextCityNameEl) {
                currentCityNameEl.textContent = cities[0]?.name || 'Город 1';
                nextCityNameEl.textContent = cities[cities.length - 1]?.name || 'Город N';
                routeInfo.style.display = 'block';
            }

            let pointIndex = startIndex;
            let lastTick = null;

            function animate(timestamp) {
                if (!isAnimating || !movingMarker) {
                    return;
                }

                if (!lastTick) {
                    lastTick = timestamp;
                }

                const elapsed = timestamp - lastTick;
                if (elapsed >= frameIntervalMs) {
                    pointIndex += speedPointsPerFrame;
                    if (pointIndex >= roadPathCoordinates.length) {
                        pointIndex = 0;
                    }

                    movingMarker.geometry.setCoordinates(roadPathCoordinates[Math.floor(pointIndex)]);

                    if (progressBar) {
                        const progress = (pointIndex / roadPathCoordinates.length) * 100;
                        progressBar.style.width = progress + '%';
                    }

                    lastTick = timestamp;
                }

                animationFrameId = requestAnimationFrame(animate);
            }

            animate();
        }

        // Обработчики кнопок
        const stopBtn = document.getElementById('stopAnimation');
        const resetBtn = document.getElementById('resetAnimation');

        if (stopBtn) {
            stopBtn.addEventListener('click', function() {
                isAnimating = false;
                if (animationFrameId) {
                    cancelAnimationFrame(animationFrameId);
                    animationFrameId = null;
                }
                const routeInfo = document.getElementById('routeInfo');
                if (routeInfo) routeInfo.style.display = 'none';
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                isAnimating = false;
                if (animationFrameId) {
                    cancelAnimationFrame(animationFrameId);
                    animationFrameId = null;
                }
                if (cities.length > 1) {
                    buildRoute();
                }

                // Вычисляем начальную позицию заново - начинаем с первого города
                let startPosition;
                let nearestIndex = 0;
                
                if (cities.length > 0) {
                    // Если есть текущий город, начинаем с него, иначе с первого
                    if (currentCityId) {
                        const currentIndex = cities.findIndex(c => c.id === currentCityId);
                        if (currentIndex !== -1) {
                            nearestIndex = currentIndex;
                            startPosition = [
                                parseFloat(cities[currentIndex].lat),
                                parseFloat(cities[currentIndex].lng)
                            ];
                        } else {
                            startPosition = [
                                parseFloat(cities[0].lat),
                                parseFloat(cities[0].lng)
                            ];
                        }
                    } else {
                        startPosition = [
                            parseFloat(cities[0].lat),
                            parseFloat(cities[0].lng)
                        ];
                    }
                }

                const routeInfo = document.getElementById('routeInfo');
                const progressBar = document.getElementById('routeProgress');
                if (routeInfo) routeInfo.style.display = 'none';
                if (progressBar) progressBar.style.width = '0%';
                if (cities.length > 0 && movingMarker && startPosition) {
                    movingMarker.geometry.setCoordinates(startPosition);
                }
                setTimeout(function() {
                    if (movingMarker && !isAnimating) {
                        animateVan();
                    }
                }, 800);
            });
        }
    </script>
@endsection
