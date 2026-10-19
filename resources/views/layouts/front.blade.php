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
    @if ($siteVerification)
        <meta name="google-site-verification" content="{{ $siteVerification }}">
    @endif

    @if ($fontsUrl)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="{{ $fontsUrl }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>{!! $themeCss !!}:root{--chrome-h:{{ $chrome }}px}@media (min-width:768px){:root{--chrome-h:{{ $chromeMd }}px}}{!! $customCss !!}</style>

    <script>
        window.SiteConfig = @js([
            'currency' => setting('currency_symbol', 'PKR'),
            'shortPrices' => setting('price_display') === 'short',
        ]);
    </script>

    @if ($analyticsId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analyticsId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @js($analyticsId));
        </script>
    @endif

    {!! $headerScripts !!}
    @stack('head')
</head>
<body class="flex min-h-screen flex-col">
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
