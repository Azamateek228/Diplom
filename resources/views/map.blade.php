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
        let vanMarker;
        let cityMarkers = [];
        let animationInterval;
        let currentRouteIndex = 0;
        let isAnimating = false;

        // Данные городов из PHP
        const cities = @json($citiesData ?? []);

        const currentCityId = @json($currentCity?->id ?? null);
        const defaultCenter = @json($defaultCenter ?? [55.7558, 37.6173]);

        console.log('Cities data:', cities);
        console.log('Default center:', defaultCenter);

        // Функция расчета расстояния между двумя точками (в км) - должна быть определена до использования
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Радиус Земли в км
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

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

            // Пытаемся найти Калугу и Воркуту для специального маршрута
            const kalugaIndex = validCities.findIndex(c =>
                c.name.toLowerCase().includes('калуг') ||
                c.name.toLowerCase().includes('kaluga')
            );
            const vorkutaIndex = validCities.findIndex(c =>
                c.name.toLowerCase().includes('воркут') ||
                c.name.toLowerCase().includes('vorkuta')
            );

            if (kalugaIndex !== -1 && vorkutaIndex !== -1) {
                // Если найдены оба города, упорядочиваем от Калуги к Воркуте
                const orderedCities = [...validCities];
                const kaluga = orderedCities[kalugaIndex];
                const vorkuta = orderedCities[vorkutaIndex];

                // Удаляем Калугу и Воркуту из массива
                orderedCities.splice(Math.max(kalugaIndex, vorkutaIndex), 1);
                orderedCities.splice(Math.min(kalugaIndex, vorkutaIndex), 1);

                // Сортируем остальные города по расстоянию от Калуги
                orderedCities.sort((a, b) => {
                    const distA = calculateDistance(
                        parseFloat(kaluga.lat), parseFloat(kaluga.lng),
                        parseFloat(a.lat), parseFloat(a.lng)
                    );
                    const distB = calculateDistance(
                        parseFloat(kaluga.lat), parseFloat(kaluga.lng),
                        parseFloat(b.lat), parseFloat(b.lng)
                    );
                    return distA - distB;
                });

                // Формируем финальный маршрут: Калуга -> остальные города -> Воркута
                return [kaluga, ...orderedCities, vorkuta];
            }

            // Если специальные города не найдены, используем алгоритм ближайшего соседа
            // Начинаем с первого города (или текущего, если он есть)
            const orderedCities = [];
            const remainingCities = [...validCities];
            
            // Если есть текущий город, начинаем с него
            let startIndex = 0;
            if (currentCityId) {
                const currentIndex = remainingCities.findIndex(c => c.id === currentCityId);
                if (currentIndex !== -1) {
                    startIndex = currentIndex;
                }
            }
            
            let currentCity = remainingCities.splice(startIndex, 1)[0];
            orderedCities.push(currentCity);

            // Находим ближайший город к текущему, пока не закончатся города
            while (remainingCities.length > 0) {
                let nearestIndex = 0;
                let minDistance = Infinity;

                remainingCities.forEach((city, idx) => {
                    const dist = calculateDistance(
                        parseFloat(currentCity.lat), parseFloat(currentCity.lng),
                        parseFloat(city.lat), parseFloat(city.lng)
                    );
                    if (dist < minDistance) {
                        minDistance = dist;
                        nearestIndex = idx;
                    }
                });

                currentCity = remainingCities.splice(nearestIndex, 1)[0];
                orderedCities.push(currentCity);
            }

            console.log('Упорядоченные города:', orderedCities.map(c => c.name));
            return orderedCities;
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
                        let startIndex = 0;

                        const adminCurrentCityIndex = currentCityId
                            ? orderedCities.findIndex(c => c.id === currentCityId)
                            : -1;

                        if (adminCurrentCityIndex !== -1) {
                            // В админке выбран текущий город — фургон стартует именно отсюда
                            const c = orderedCities[adminCurrentCityIndex];
                            startPosition = [parseFloat(c.lat), parseFloat(c.lng)];
                            startIndex = adminCurrentCityIndex;
                        } else if (orderedCities.length >= 2) {
                            // Иначе — по умолчанию: ближе к первому городу маршрута
                            const firstCity = orderedCities[0];
                            startPosition = [
                                parseFloat(firstCity.lat),
                                parseFloat(firstCity.lng)
                            ];
                            startIndex = 0;
                        } else {
                            startPosition = [
                                parseFloat(orderedCities[0].lat),
                                parseFloat(orderedCities[0].lng)
                            ];
                        }

                        // Создаем кастомную иконку фургончика с изображением
                        const vanImageUrl = '{{ asset('images/furgon.png') }}';
                        
                        // Проверяем наличие изображения, если нет - используем стандартную иконку
                        vanMarker = new ymaps.Placemark(
                            startPosition, {
                                balloonContent: '<strong>Кинотеатр на колёсах</strong><br>Текущее местоположение'
                            }, {
                                iconLayout: 'default#image',
                                iconImageHref: vanImageUrl,
                                iconImageSize: [80, 80],
                                iconImageOffset: [-40, -40],
                                // Fallback на стандартную иконку, если изображение не загрузится
                                preset: 'islands#yellowAutoIcon'
                            }
                        );
                        
                        // Проверяем загрузку изображения
                        const img = new Image();
                        img.onerror = function() {
                            console.warn('Изображение фургончика не найдено, используется стандартная иконка');
                            vanMarker.options.set('preset', 'islands#yellowAutoIcon');
                            vanMarker.options.unset('iconLayout');
                            vanMarker.options.unset('iconImageHref');
                            vanMarker.options.unset('iconImageSize');
                            vanMarker.options.unset('iconImageOffset');
                        };
                        img.src = vanImageUrl;

                        map.geoObjects.add(vanMarker);

                        // Обновляем массив cities для анимации (упорядоченный)
                        cities.length = 0;
                        cities.push(...orderedCities);

                        // Устанавливаем начальный индекс для анимации
                        currentRouteIndex = startIndex;
                    }

                    // Строим маршрут между городами
                    if (cities.length > 1) {
                        buildRoute();

                        // Автоматически запускаем анимацию через 1 секунду после загрузки карты
                        setTimeout(function() {
                            if (vanMarker && !isAnimating) {
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

        // Функция построения маршрута
        function buildRoute() {
            if (cities.length < 2) return;

            // Удаляем старый маршрут, если он существует
            if (routePolyline) {
                map.geoObjects.remove(routePolyline);
                routePolyline = null;
            }

            // Упорядочиваем города
            const orderedCities = orderCities(cities);
            
            // Проверяем валидность координат перед построением маршрута
            const routePoints = orderedCities
                .map(city => {
                    const lat = parseFloat(city.lat);
                    const lng = parseFloat(city.lng);
                    if (isNaN(lat) || isNaN(lng)) {
                        console.warn(`Пропущен город "${city.name}" из-за невалидных координат`);
                        return null;
                    }
                    return [lat, lng];
                })
                .filter(point => point !== null);
            
            if (routePoints.length < 2) {
                console.warn('Недостаточно точек для построения маршрута');
                return;
            }

            // Создаем полилинию маршрута
            routePolyline = new ymaps.Polyline(
                routePoints, {}, {
                    strokeColor: '#ffcc00',
                    strokeWidth: 4,
                    strokeStyle: '5 5' // Пунктирная линия
                }
            );

            map.geoObjects.add(routePolyline);

            // Подстраиваем карту под маршрут
            try {
                map.setBounds(routePolyline.geometry.getBounds(), {
                    checkZoomRange: true,
                    duration: 500
                });
            } catch (error) {
                console.warn('Ошибка при подстройке карты под маршрут:', error);
            }
        }

        // Функция анимации движения фургончика
        function animateVan() {
            if (cities.length < 2 || isAnimating) return;

            isAnimating = true;
            // Используем текущий индекс (может быть установлен при инициализации)
            if (currentRouteIndex >= cities.length) {
                currentRouteIndex = 0;
            }

            // Убеждаемся, что маршрут упорядочен
            const orderedCities = orderCities(cities);
            cities.length = 0;
            cities.push(...orderedCities);

            function moveToNextCity() {
                // Определяем следующий город (зацикливаем маршрут)
                const nextIndex = currentRouteIndex >= cities.length - 1 ? 0 : currentRouteIndex + 1;

                const currentCity = cities[currentRouteIndex];
                const nextCity = cities[nextIndex];

                // Проверяем валидность данных городов
                if (!currentCity || !nextCity) {
                    console.error('Ошибка: не найдены города для анимации');
                    isAnimating = false;
                    return;
                }

                // Вычисляем расстояние между городами для более реалистичной скорости
                const distance = calculateDistance(
                    parseFloat(currentCity.lat), parseFloat(currentCity.lng),
                    parseFloat(nextCity.lat), parseFloat(nextCity.lng)
                );

                // Базовое время: 30 секунд на 100 км, минимум 20 секунд (замедлено)
                const baseDuration = Math.max(20000, (distance / 100) * 30000);

                // Анимируем движение между городами с реалистичной скоростью
                animateBetweenCities(
                    [parseFloat(currentCity.lat), parseFloat(currentCity.lng)],
                    [parseFloat(nextCity.lat), parseFloat(nextCity.lng)],
                    baseDuration, // Динамическое время в зависимости от расстояния
                    nextIndex, // Передаем индекс следующего города
                    () => {
                        // Переходим к следующему городу
                        currentRouteIndex = nextIndex;
                        if (isAnimating) {
                            // Пауза в городе: 6 секунд (замедлено)
                            setTimeout(moveToNextCity, 6000);
                        }
                    }
                );
            }

            moveToNextCity();
        }

        // Функция плавной анимации между двумя точками
        function animateBetweenCities(start, end, duration, nextIndex, callback) {
            const startTime = Date.now();
            const startLat = start[0];
            const startLng = start[1];
            const deltaLat = end[0] - startLat;
            const deltaLng = end[1] - startLng;

            // Показываем информацию о маршруте
            const routeInfo = document.getElementById('routeInfo');
            const currentCityNameEl = document.getElementById('currentCityName');
            const nextCityNameEl = document.getElementById('nextCityName');
            const progressBar = document.getElementById('routeProgress');

            if (routeInfo && currentCityNameEl && nextCityNameEl) {
                const currentCity = cities[currentRouteIndex];
                const nextCity = cities[nextIndex];
                currentCityNameEl.textContent = currentCity.name;
                nextCityNameEl.textContent = nextCity.name;
                routeInfo.style.display = 'block';
            }

            function animate() {
                if (!vanMarker) {
                    console.error('Маркер фургончика не создан');
                    if (callback) callback();
                    return;
                }

                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Используем более плавную easing функцию для реалистичного движения
                const easeProgress = easeInOutCubic(progress);

                const currentLat = startLat + deltaLat * easeProgress;
                const currentLng = startLng + deltaLng * easeProgress;

                vanMarker.geometry.setCoordinates([currentLat, currentLng]);

                // Обновляем прогресс-бар
                if (progressBar) {
                    progressBar.style.width = (progress * 100) + '%';
                }

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    if (progressBar) {
                        progressBar.style.width = '0%';
                    }
                    if (callback) callback();
                }
            }

            animate();
        }

        // Easing функция для плавной анимации (более реалистичная)
        function easeInOutQuad(t) {
            return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
        }

        // Более плавная easing функция для реалистичного движения
        function easeInOutCubic(t) {
            return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
        }

        // Обработчики кнопок
        const startBtn = document.getElementById('startAnimation');
        const stopBtn = document.getElementById('stopAnimation');
        const resetBtn = document.getElementById('resetAnimation');

        if (stopBtn) {
            stopBtn.addEventListener('click', function() {
                isAnimating = false;
                const routeInfo = document.getElementById('routeInfo');
                if (routeInfo) routeInfo.style.display = 'none';
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                isAnimating = false;
                // Упорядочиваем города
                const orderedCities = orderCities(cities);
                cities.length = 0;
                cities.push(...orderedCities);

                // Перестраиваем маршрут
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
                if (cities.length > 0 && vanMarker && startPosition) {
                    vanMarker.geometry.setCoordinates(startPosition);
                    currentRouteIndex = nearestIndex;
                }
            });
        }
    </script>
@endsection
