# ВНЕШНЯЯ SEO-ОПТИМИЗАЦИЯ И РЕГИСТРАЦИЯ В ВЕБМАСТЕРАХ
## Проект: Кинотеатр на колёсах

---

## 1. РЕГИСТРАЦИЯ В ПОИСКОВЫХ СИСТЕМАХ

### 1.1 Яндекс.Вебмастер

**Шаг 1: Регистрация**
1. Перейти на https://webmaster.yandex.ru
2. Войти через Яндекс.Почту
3. Нажать "+" (Добавить сайт)

**Шаг 2: Подтверждение прав**

**Способ A: HTML-файл (рекомендуется)**
```
1. Скачать файл verification.html
2. Загрузить в корень сайта: /public/verification.html
3. Нажать "Проверить"
```

**Способ B: Meta-тег**
```html
<!-- Добавить в <head> на главной странице -->
<meta name="yandex-verification" content="xxxxxxxxxxxxxxxx" />
```

**Способ C: Через DNS**
```
Добавить TXT-запись:
yandex-verification: xxxxxxxxxxxxxxxx
```

**Шаг 3: Настройка**
1. Указать главное зеркало: https://kinokolesa.ru
2. Добавить sitemap.xml: https://kinokolesa.ru/sitemap.xml
3. Настроить robots.txt (уже готов)
4. Включить индексацию важных страниц

**Шаг 4: Мониторинг**
- Проверка индексации
- Анализ поисковых запросов
- Отслеживание позиций
- Мониторинг ошибок сканирования

**URL:** https://webmaster.yandex.ru

---

### 1.2 Google Search Console

**Шаг 1: Регистрация**
1. Перейти на https://search.google.com/search-console
2. Войти через Google-аккаунт
3. Выбрать тип ресурса: "Домен" или "URL-префикс"

**Шаг 2: Подтверждение прав**

**Для домена (рекомендуется):**
```
Добавить DNS-запись:
Type: TXT
Name: @
Value: google-site-verification=xxxxxxxxxxxxx
```

**Для URL-префикса:**
- HTML-файл (как для Яндекса)
- HTML-тег
- Google Analytics
- Google Tag Manager

**Шаг 3: Настройка**
1. Добавить sitemap.xml
2. Проверить покрытие индексирования
3. Настроить параметры URL
4. Включить улучшения (Rich Results)

**Шаг 4: Мониторинг**
- Эффективность в поиске
- Покрытие индексирования
- Удобство для мобильных
- Основные веб-показатели

**URL:** https://search.google.com/search-console

---

### 1.3 Bing Webmaster Tools

**Шаг 1: Регистрация**
1. Перейти на https://www.bing.com/webmasters
2. Войти через Microsoft-аккаунт
3. Добавить сайт

**Шаг 2: Подтверждение**
- Импорт из Google Search Console (быстро)
- HTML-файл
- Meta-тег

**Шаг 3: Настройка**
- Добавить sitemap.xml
- Настроить сканирование

**URL:** https://www.bing.com/webmasters

---

## 2. НАСТРОЙКА АНАЛИТИКИ

### 2.1 Яндекс.Метрика

**Шаг 1: Создание счетчика**
1. https://metrika.yandex.ru
2. Нажать "Добавить счетчик"
3. Заполнить данные:
   - Название: Кинотеатр на колёсах
   - Сайт: https://kinokolesa.ru
   - Часовой пояс: Москва
   - Валюта: RUB

**Шаг 2: Настройка**
```
✅ Включить вебвизор
✅ Включить карту ссылок
✅ Включить аналитику форм
✅ Отключить индексацию (для админки)
✅ Включить электронную коммерцию (если нужно)
```

**Шаг 3: Установка кода**

Код уже добавлен в `/resources/views/partials/analytics.blade.php`:

```html
<!-- Яндекс.Метрика -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();
    for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
    k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

    ym(XXXXXXXX, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
    });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/XXXXXXXX" style="position:absolute; left:-9999px;" alt="Яндекс.Метрика" /></div></noscript>
```

**Замените XXXXXXXX на номер вашего счетчика!**

**Шаг 4: Настройка целей**

```
Цель 1: Регистрация
Тип: JavaScript-событие
ID цели: registration

Цель 2: Голосование за фильм
Тип: JavaScript-событие
ID цели: vote_movie

Цель 3: Вход в систему
Тип: JavaScript-событие
ID цели: login

Цель 4: Просмотр карты
Тип: JavaScript-событие
ID цели: view_map
```

**Добавление отслеживания событий:**

```javascript
// Регистрация
ym(XXXXXXXX, 'reachGoal', 'registration');

// Голосование
ym(XXXXXXXX, 'reachGoal', 'vote_movie', {
    movie_id: movieId,
    city_id: cityId
});

// Вход
ym(XXXXXXXX, 'reachGoal', 'login');
```

---

### 2.2 Google Analytics 4

**Шаг 1: Создание ресурса**
1. https://analytics.google.com
2. Создать аккаунт (если нет)
3. Создать ресурс GA4:
   - Название: Кинотеатр на колёсах
   - Часовой пояс: Россия/Москва
   - Валюта: RUB

**Шаг 2: Создание потока данных**
1. Тип: Веб
2. URL сайта: kinokolesa.ru
3. Название потока: Основной сайт

**Шаг 3: Установка кода**

Код уже добавлен в `/resources/views/partials/analytics.blade.php`:

```html
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-XXXXXXXXXX', {
        'anonymize_ip': true,
        'send_page_view': true
    });
</script>
```

**Замените G-XXXXXXXXXX на ваш номер!**

**Шаг 4: Настройка событий**

```javascript
// Регистрация
gtag('event', 'sign_up', {
    'method': 'email'
});

// Голосование
gtag('event', 'vote', {
    'event_category': 'engagement',
    'event_label': 'movie_vote',
    'movie_id': movieId
});

// Вход
gtag('event', 'login', {
    'method': 'email'
});

// Просмотр карты
gtag('event', 'view_map', {
    'event_category': 'engagement'
});
```

---

### 2.3 Google Tag Manager (опционально)

**Шаг 1: Создание контейнера**
1. https://tagmanager.google.com
2. Создать аккаунт
3. Создать контейнер:
   - Название: Кинотеатр на колёсах
   - Тип: Веб

**Шаг 2: Установка кода**

Код уже добавлен в `/resources/views/partials/analytics.blade.php`:

```html
<!-- Google Tag Manager -->
<script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-XXXXXX');
</script>
```

**Замените GTM-XXXXXX на ваш номер!**

**Шаг 3: Настройка тегов в GTM**
- Яндекс.Метрика
- Google Analytics
- Пиксель ВКонтакте
- Facebook Pixel

---

## 3. ВНЕШНЯЯ SEO-ОПТИМИЗАЦИЯ

### 3.1 Регистрация в каталогах

**Обязательные каталоги:**

| Каталог | URL | Стоимость |
|---------|-----|-----------|
| Яндекс.Карты | https://yandex.ru/maps | Бесплатно |
| Google Мой Бизнес | https://google.com/business | Бесплатно |
| 2ГИС | https://2gis.ru | Бесплатно |
| Zoon.ru | https://zoon.ru | Бесплатно |
| Afisha.ru | https://afisha.ru | Бесплатно |

---

### 3.2 Социальные сети

**Создание страниц:**

**ВКонтакте:**
1. Создать сообщество: https://vk.com
2. Тип: Организация / Мероприятие
3. Название: Кинотеатр на колёсах
4. Добавить информацию, контакты, ссылки
5. Регулярно публиковать контент

**Telegram:**
1. Создать канал: https://telegram.org
2. Название: @kinokolesa
3. Публиковать анонсы показов

**Одноклассники:**
1. Создать группу: https://ok.ru
2. Для аудитории 35+

---

### 3.3 Работа с отзывами

**Площадки для отзывов:**

| Площадка | URL |
|----------|-----|
| Яндекс.Карты | https://yandex.ru/maps |
| Google Карты | https://google.com/maps |
| 2ГИС | https://2gis.ru |
| Zoon.ru | https://zoon.ru |
| Flamp.ru | https://flamp.ru |

**Стратегия работы:**
1. Отвечать на все отзывы (положительные и отрицательные)
2. Быть вежливыми и конструктивными
3. Решать проблемы клиентов публично
4. Поощрять положительные отзывы

---

### 3.4 Крауд-маркетинг

**Форумы и площадки:**

| Площадка | Описание |
|----------|----------|
| Pikabu | Посты о мероприятиях |
| Reddit (r/russia) | Для международной аудитории |
| Городские форумы | Региональные обсуждения |
| TripAdvisor | Для туристов |

**Правила:**
- Не спамить!
- Давать полезную информацию
- Упоминать проект естественно

---

### 3.5 Гостевой блогинг

**Площадки для публикаций:**

1. Городские порталы
2. Кино-блоги
3. Культурные сайты
4. Туристические ресурсы

**Темы статей:**
- "Как выездной кинотеатр развивает культуру в малых городах"
- "История мобильного кино в России"
- "Топ-10 фильмов для просмотра под открытым небом"

---

## 4. КОНТРОЛЬНЫЙ СПИСОК

### После регистрации:

- [ ] Яндекс.Вебмастер: сайт добавлен, sitemap загружен
- [ ] Google Search Console: сайт добавлен, sitemap загружен
- [ ] Bing Webmaster: сайт добавлен
- [ ] Яндекс.Метрика: счетчик установлен, цели настроены
- [ ] Google Analytics: код установлен, события настроены
- [ ] Google Tag Manager: контейнер настроен (опционально)
- [ ] Яндекс.Карты: организация добавлена
- [ ] Google Мой Бизнес: организация добавлена
- [ ] Соцсети: страницы созданы
- [ ] Отзывы: мониторинг настроен

---

## 5. МОНИТОРИНГ И ОТЧЁТНОСТЬ

### Еженедельно:

1. Проверка позиций в поиске
2. Анализ трафика (Яндекс.Метрика, GA)
3. Проверка ошибок сканирования
4. Мониторинг отзывов

### Ежемесячно:

1. Отчёт по трафику
2. Отчёт по конверсиям
3. Анализ поисковых запросов
4. План работ на следующий месяц

---

*Документ составлен: 15 марта 2026 г.*
*Для проекта: Кинотеатр на колёсах*
