@props(['seo'])

<title>{{ $seo->title }}</title>
@if ($seo->description)
    <meta name="description" content="{{ $seo->description }}">
@endif
@if ($seo->keywords)
    <meta name="keywords" content="{{ $seo->keywords }}">
@endif
@if ($seo->robots)
    <meta name="robots" content="{{ $seo->robots }}">
@endif
<link rel="canonical" href="{{ $seo->canonical }}">

<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:site_name" content="{{ setting('site_name') }}">
<meta property="og:title" content="{{ $seo->ogTitle }}">
@if ($seo->ogDescription)
    <meta property="og:description" content="{{ $seo->ogDescription }}">
@endif
<meta property="og:url" content="{{ $seo->canonical }}">
@if ($seo->ogImage)
    <meta property="og:image" content="{{ $seo->ogImage }}">
    <meta name="twitter:image" content="{{ $seo->ogImage }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->ogTitle }}">
@if ($seo->ogDescription)
    <meta name="twitter:description" content="{{ $seo->ogDescription }}">
@endif

@foreach ($seo->schemas as $schema)
    <script type="application/ld+json">{!! $schema !!}</script>
@endforeach
