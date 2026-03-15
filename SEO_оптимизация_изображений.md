# ОПТИМИЗАЦИЯ ИЗОБРАЖЕНИЙ
## Проект: Кинотеатр на колёсах

---

## 1. АУДИТ ТЕКУЩИХ ИЗОБРАЖЕНИЙ

### Текущие файлы в `/public/images/`:

| Файл | Размер | Формат | Назначение | Проблемы |
|------|--------|--------|------------|----------|
| banner1.png | ? | PNG | Главный баннер | PNG большой размер |
| film1.jpg - film5.jpg | ? | JPG | Постеры фильмов | Нет alt/title |
| furgon.png | ? | PNG | Иконка фургона | Можно оптимизировать |
| map.jpg | ? | JPG | Фон карты | Не используется |

---

## 2. РЕКОМЕНДАЦИИ ПО ОПТИМИЗАЦИИ

### 2.1 Форматы изображений

**Для фотографий (постеры фильмов):**
- ✅ Использовать **WebP** (современный формат, на 25-35% меньше JPG)
- ✅ Резервный JPG для старых браузеров
- ✅ Качество: 80-85% (оптимально для веба)

**Для графики с прозрачностью (фургон, иконки):**
- ✅ Использовать **WebP с прозрачностью**
- ✅ PNG только если WebP не поддерживается
- ✅ SVG для простых иконок

**Для баннеров и фонов:**
- ✅ WebP с прогрессивной загрузкой
- ✅ Lazy loading для изображений ниже первого экрана

---

### 2.2 Размеры изображений

| Изображение | Рекомендуемый размер | Макс. размер файла |
|-------------|---------------------|-------------------|
| Главный баннер | 1920×600 px | < 200 KB |
| Постер фильма (карточка) | 400×600 px | < 100 KB |
| Постер фильма (полный) | 800×1200 px | < 250 KB |
| Иконка фургона | 80×80 px | < 20 KB |
| Фоновые изображения | 1920×1080 px | < 300 KB |

---

### 2.3 Атрибуты ALT и TITLE

**Правила заполнения:**

```html
<!-- Правильно -->
<img src="poster.jpg" 
     alt="Постер фильма Название Фильма 2026" 
     title="Название Фильма — смотреть онлайн"
     loading="lazy">

<!-- Неправильно -->
<img src="poster.jpg">
<img alt="изображение">
<img alt="film1.jpg">
```

**Шаблон для постеров фильмов:**
- **Alt:** "Постер фильма [Название] ([Год])"
- **Title:** "[Название] — [жанр], [возраст]+"

**Примеры:**

| Изображение | ALT | TITLE |
|-------------|-----|-------|
| film1.jpg | Постер фильма Дюна (2025) | Дюна — фантастика, 16+ |
| film2.jpg | Постер фильма Опенгеймер (2024) | Опенгеймер — драма, 18+ |
| furgon.png | Иконка фургона кинотеатра на колёсах | Кинотеатр на колёсах |
| banner1.png | Баннер кинотеатра на колёсах — выездные показы кино | Кинотеатр на колёсах в Татарстане |

---

## 3. IMPLEMENTATION В LARAVEL

### 3.1 Обновлённые Blade-шаблоны

**movies/index.blade.php:**
```blade
<img class="movie-poster card-img-top"
    src="{{ $movie->poster ? asset($movie->poster) : asset('images/poster-placeholder.webp') }}"
    alt="Постер фильма: {{ $movie->title }}"
    title="{{ $movie->title }} — {{ $movie->genre ?? 'Фильм' }}"
    loading="lazy"
    width="400"
    height="600"
    style="height: 400px; object-fit: cover;">
```

**home.blade.php:**
```blade
<section class="hero" style="background-image: url('{{ asset('images/banner1.webp') }}');">
    <!-- Контент -->
</section>
```

---

### 3.2 Lazy Loading

**Для всех изображений ниже первого экрана:**
```html
<img src="image.jpg" loading="lazy" alt="..." title="...">
```

**Для фоновых изображений:**
```css
.hero {
    background-image: url('/images/banner1.webp');
    background-size: cover;
    background-position: center;
}
```

---

## 4. ИНСТРУМЕНТЫ ДЛЯ ОПТИМИЗАЦИИ

### 4.1 Онлайн-сервисы

| Сервис | Назначение | URL |
|--------|------------|-----|
| TinyPNG | Сжатие PNG/JPG | https://tinypng.com |
| Squoosh | Конвертация в WebP | https://squoosh.app |
| Compressor.io | Сжатие без потерь | https://compressor.io |
| Ezgif | Оптимизация GIF | https://ezgif.com |

### 4.2 Локальные утилиты

**ImageOptim (Mac):**
```bash
brew install --cask imageoptim
```

**FileOptimizer (Windows):**
```
Скачать: https://www.nikkhokkho.dk/
```

**Command-line инструменты:**
```bash
# Конвертация в WebP
cwebp -q 80 input.jpg -o output.webp

# Оптимизация PNG
pngquant --quality=65-80 input.png

# Оптимизация JPG
jpegoptim --max=85 input.jpg
```

### 4.3 Laravel-пакеты

**spatie/laravel-image-optimizer:**
```bash
composer require spatie/laravel-image-optimizer
```

**intervention/image:**
```bash
composer require intervention/image
```

**Пример использования:**
```php
use Intervention\Image\Facades\Image;

$image = Image::make($request->file('poster'));
$image->resize(400, 600);
$image->encode('webp', 85);
$image->save(public_path('images/poster.webp'));
```

---

## 5. ПЛАН ДЕЙСТВИЙ

### Этап 1: Подготовка (1 день)
1. [ ] Аудит всех изображений на сайте
2. [ ] Замер текущих размеров файлов
3. [ ] Составление списка для оптимизации

### Этап 2: Оптимизация (2 дня)
1. [ ] Конвертация в WebP
2. [ ] Сжатие без видимой потери качества
3. [ ] Изменение размеров при необходимости
4. [ ] Добавление alt и title атрибутов

### Этап 3: Внедрение (1 день)
1. [ ] Обновление Blade-шаблонов
2. [ ] Добавление loading="lazy"
3. [ ] Тестирование в браузерах

### Этап 4: Проверка (1 день)
1. [ ] Замер скорости загрузки (PageSpeed Insights)
2. [ ] Проверка индексации (Google Search Console)
3. [ ] Мониторинг трафика

---

## 6. ОЖИДАЕМЫЕ РЕЗУЛЬТАТЫ

### До оптимизации:
- Общий размер страницы: ~3-5 MB
- Время загрузки: 4-6 секунд
- PageSpeed Score: 50-65

### После оптимизации:
- Общий размер страницы: ~1-2 MB
- Время загрузки: 1-2 секунды
- PageSpeed Score: 85-95

**Улучшение производительности: 60-70%**

---

## 7. МИКРОРАЗМЕТКА ДЛЯ ИЗОБРАЖЕНИЙ

**Schema.org ImageObject:**
```json
{
  "@context": "https://schema.org",
  "@type": "ImageObject",
  "contentUrl": "https://kinokolesa.ru/images/banner1.webp",
  "description": "Баннер кинотеатра на колёсах",
  "caption": "Выездной кинотеатр в Татарстане",
  "width": 1920,
  "height": 600
}
```

---

## 8. ПРОВЕРКА РЕЗУЛЬТАТОВ

### Google PageSpeed Insights:
https://pagespeed.web.dev/

### GTmetrix:
https://gtmetrix.com/

### WebPageTest:
https://www.webpagetest.org/

### Критерии успеха:
- ✅ Все изображения имеют alt атрибут
- ✅ Используется современный формат (WebP)
- ✅ Lazy loading для изображений ниже fold
- ✅ Размер изображений оптимизирован
- ✅ PageSpeed Score > 85

---

*Документ составлен: 15 марта 2026 г.*
*Для проекта: Кинотеатр на колёсах*
