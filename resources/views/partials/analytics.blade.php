{{-- 
    Аналитика: Яндекс.Метрика и Google Analytics
    Замените XXXXXXXX на ваши реальные номера счетчиков
--}}

{{-- Яндекс.Метрика --}}
@env('production')
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();
    for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
    k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

    // Замените XXXXXXXX на номер вашего счетчика Яндекс.Метрики
    ym(XXXXXXXX, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
    });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/XXXXXXXX" style="position:absolute; left:-9999px;" alt="Яндекс.Метрика" /></div></noscript>
@endenv

{{-- Google Analytics 4 --}}
@env('production')
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    
    // Замените G-XXXXXXXXXX на ваш номер Google Analytics 4
    gtag('config', 'G-XXXXXXXXXX', {
        'anonymize_ip': true,
        'send_page_view': true
    });
</script>
@endenv

{{-- Google Tag Manager (опционально) --}}
@env('production')
<script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-XXXXXX');
</script>
@endenv
