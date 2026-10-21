@props(['seo'])

<title>{{ $seo->title }}</title>
@if ($seo->description)
    <meta name="description" content="{{ $seo->description }}">
@endif
@if ($seo->keywords)
    <meta name="keywords" content="{{ $seo->keywords }}">
@endif
<meta name="robots" content="{{ $seo->robots }}">
@if ($seo->publisher)
    <meta name="author" content="{{ $seo->publisher }}">
    <meta name="publisher" content="{{ $seo->publisher }}">
@endif
<link rel="canonical" href="{{ $seo->canonical }}">
@foreach ($seo->geo as $name => $content)
    <meta name="geo.{{ $name }}" content="{{ $content }}">
@endforeach
@isset($seo->geo['position'])
    <meta name="ICBM" content="{{ str_replace(';', ', ', $seo->geo['position']) }}">
@endisset

<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:locale" content="{{ $seo->locale }}">
<meta property="og:site_name" content="{{ setting('site_name') }}">
<meta property="og:title" content="{{ $seo->ogTitle }}">
@if ($seo->ogDescription)
    <meta property="og:description" content="{{ $seo->ogDescription }}">
@endif
<meta property="og:url" content="{{ $seo->canonical }}">
@if ($seo->ogImage)
    <meta property="og:image" content="{{ $seo->ogImage }}">
    <meta property="og:image:alt" content="{{ $seo->ogTitle }}">
    <meta name="twitter:image" content="{{ $seo->ogImage }}">
    <meta name="twitter:image:alt" content="{{ $seo->ogTitle }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->ogTitle }}">
@if ($seo->ogDescription)
    <meta name="twitter:description" content="{{ $seo->ogDescription }}">
@endif

@foreach ($seo->schemas as $schema)
    <script type="application/ld+json">{!! $schema !!}</script>
@endforeach
