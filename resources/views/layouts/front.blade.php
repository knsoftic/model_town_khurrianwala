@php
    $seo ??= app(\App\Services\SeoService::class)->make();
    $isHome = request()->routeIs('home');
    // Height of demo bar (28px) + top bar (40px, md+) + header (76px); the homepage hero
    // slides under the transparent header by this amount.
    $chrome = 76 + (setting_enabled('demo_notice_enabled') && setting('demo_notice_text') ? 28 : 0);
    $chromeMd = $chrome + (setting_enabled('topbar_enabled') ? 40 : 0);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-front.seo :seo="$seo" />

    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    {{-- Site-ownership tags from Settings → Verification & Analytics. --}}
    @foreach ($verificationTags as $tag)
        <meta {{ $tag['attribute'] }}="{{ $tag['name'] }}" content="{{ $tag['content'] }}">
    @endforeach

    @stack('head')

    {{-- Fonts load without holding up the first paint; text shows in the fallback font until they arrive. --}}
    @if ($fontsUrl)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="{{ $fontsUrl }}" media="print" onload="this.media='all'">
        <noscript><link rel="stylesheet" href="{{ $fontsUrl }}"></noscript>
    @endif

    {{-- The stylesheet is printed inline in production, saving a round trip before the page can render. --}}
    @if (config('cms.inline_css') && ! \Illuminate\Support\Facades\Vite::isRunningHot())
        <style>{!! \Illuminate\Support\Facades\Vite::content('resources/css/app.css') !!}</style>
        @vite('resources/js/app.js')
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>{!! $themeCss !!}:root{--chrome-h:{{ $chrome }}px}@media (min-width:768px){:root{--chrome-h:{{ $chromeMd }}px}}{!! $customCss !!}</style>

    <script>
        window.SiteConfig = @js([
            'currency' => setting('currency_symbol', 'PKR'),
            'shortPrices' => setting('price_display') === 'short',
        ]);
    </script>

    {{-- Google Analytics / Tag Manager. Events queue in dataLayer straight away; the scripts
         themselves load on the first scroll or tap (or after 6 s) when "analytics_delay" is on. --}}
    @if ($analyticsId || $tagManagerId)
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            @if ($tagManagerId)
                dataLayer.push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
            @endif
            @if ($analyticsId)
                gtag('js', new Date());
                gtag('config', @js($analyticsId));
            @endif
            (function () {
                var sources = @js(array_values(array_filter([
                    $analyticsId ? 'https://www.googletagmanager.com/gtag/js?id='.$analyticsId : null,
                    $tagManagerId ? 'https://www.googletagmanager.com/gtm.js?id='.$tagManagerId : null,
                ])));
                var loaded = false;
                function load() {
                    if (loaded) return;
                    loaded = true;
                    sources.forEach(function (src) {
                        var script = document.createElement('script');
                        script.async = true;
                        script.src = src;
                        document.head.appendChild(script);
                    });
                }
                @if ($analyticsDelay)
                    ['scroll', 'pointerdown', 'keydown', 'touchstart'].forEach(function (name) {
                        window.addEventListener(name, load, { once: true, passive: true });
                    });
                    window.addEventListener('load', function () { setTimeout(load, 6000); });
                @else
                    load();
                @endif
            })();
        </script>
    @endif

    {!! $headerScripts !!}
</head>
<body class="flex min-h-screen flex-col">
    @if ($tagManagerId)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $tagManagerId }}" title="Google Tag Manager" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-primary">Skip to content</a>

    @include('front.partials.header', ['transparent' => $isHome && setting_enabled('header_transparent_home')])

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    @include('front.partials.footer')
    @include('front.partials.whatsapp')
    @include('front.partials.callback-modal')

    @if ($customJs)
        <script>{!! $customJs !!}</script>
    @endif
    {!! $footerScripts !!}
    @stack('scripts')
</body>
</html>
