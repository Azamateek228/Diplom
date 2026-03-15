{{-- 
    Микроразметка Schema.org для SEO
    https://schema.org
--}}

{{-- Организация (LocalBusiness) --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "Кинотеатр на колёсах",
    "description": "Уникальный выездной кинотеатр на колёсах. Показ фильмов под открытым небом в городах России.",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('images/banner1.png') }}",
    "image": "{{ asset('images/banner1.png') }}",
    "telephone": "+7-XXX-XXX-XX-XX",
    "email": "info@kinokolesa.ru",
    "address": {
        "@type": "PostalAddress",
        "addressCountry": "Россия",
        "addressRegion": "Татарстан"
    },
    "openingHours": "Mo-Su 10:00-22:00",
    "priceRange": "₽₽",
    "sameAs": [
        "https://vk.com/kinokolesa",
        "https://t.me/kinokolesa"
    ]
}
</script>

{{-- Movie (для страниц фильмов) --}}
@if (isset($movie) && $movie)
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Movie",
    "name": "{{ $movie->title }}",
    "description": "{{ Str::limit($movie->description, 200) }}",
    "image": "{{ $movie->poster ? asset($movie->poster) : asset('images/poster-placeholder.jpg') }}",
    "genre": "{{ $movie->genre ?? 'Не указан' }}",
    "duration": "PT{{ $movie->duration ?? 90 }}M",
    "contentRating": "{{ $movie->age_rating ?? '16' }}+",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "{{ number_format($movie->rating / max(1, $movie->votes_count ?? 1), 1) }}",
        "bestRating": "10",
        "worstRating": "1",
        "ratingCount": "{{ $movie->votes_count ?? 0 }}"
    }
}
</script>
@endif

{{-- Event (для мероприятий) --}}
@if (isset($routes) && $routes->isNotEmpty())
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Event",
    "name": "Показ фильма выездным кинотеатром",
    "description": "Показ фильмов под открытым небом выездным кинотеатром на колёсах",
    "image": "{{ asset('images/banner1.png') }}",
    "startDate": "{{ now()->toIso8601String() }}",
    "endDate": "{{ now()->addDays(7)->toIso8601String() }}",
    "eventStatus": "https://schema.org/EventScheduled",
    "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
    "location": {
        "@type": "Place",
        "name": "{{ $routes->first()->city->name ?? 'Город показа' }}",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "Россия",
            "addressRegion": "Татарстан"
        }
    },
    "organizer": {
        "@type": "Organization",
        "name": "Кинотеатр на колёсах",
        "url": "{{ url('/') }}"
    },
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "RUB",
        "availability": "https://schema.org/InStock"
    }
}
</script>
@endif

{{-- BreadcrumbList (хлебные крошки) --}}
@if (isset($breadcrumbs) && is_array($breadcrumbs))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        @foreach($breadcrumbs as $index => $breadcrumb)
        {
            "@type": "ListItem",
            "position": {{ $index + 1 }},
            "name": "{{ $breadcrumb['name'] }}",
            "item": "{{ $breadcrumb['url'] }}"
        }@if (!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif

{{-- WebSite (для главной страницы) --}}
@if (request()->routeIs('home'))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Кинотеатр на колёсах",
    "alternateName": "Кино на колёсах",
    "url": "{{ url('/') }}",
    "description": "Уникальный выездной кинотеатр на колёсах. Голосуйте за фильмы, выбирайте маршрут следования.",
    "inLanguage": "ru",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ url('/movies') }}?search={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
@endif
