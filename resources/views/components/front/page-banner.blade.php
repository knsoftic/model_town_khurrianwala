@props(['page' => null, 'eyebrow' => null, 'heading' => null, 'subheading' => null, 'image' => null, 'crumbs' => []])

@php
    $eyebrow ??= $page?->eyebrow;
    $heading ??= $page?->heading ?? $page?->name;
    $subheading ??= $page?->subheading;
    $imagePath = $image ?? $page?->banner_image;
@endphp

<section class="relative isolate overflow-hidden bg-primary text-white">
    @if ($imagePath)
        @push('head'){{ img_preload($imagePath, '100vw', [640, 960, 1280, 1920]) }}@endpush
        <img {{ img_attrs($imagePath, '100vw', [640, 960, 1280, 1920]) }} alt="" class="absolute inset-0 -z-20 size-full object-cover" fetchpriority="high">
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-primary via-primary/90 to-primary/55"></div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-24 bg-gradient-to-t from-primary/60 to-transparent"></div>

    <div class="container-x py-16 sm:py-20 lg:py-24">
        <nav aria-label="Breadcrumb" class="mb-6 flex flex-wrap items-center gap-1.5 text-xs font-medium text-white/60">
            <a href="{{ route('home') }}" class="hover:text-accent">Home</a>
            @foreach ($crumbs as $label => $url)
                <x-icon name="chevron-right" class="size-3.5" />
                @if ($url)
                    <a href="{{ $url }}" class="hover:text-accent">{{ $label }}</a>
                @else
                    <span class="text-white/85" aria-current="page">{{ $label }}</span>
                @endif
            @endforeach
        </nav>

        <div class="max-w-3xl">
            @if ($eyebrow)
                <span class="eyebrow eyebrow-on-dark">{{ $eyebrow }}</span>
            @endif
            @if ($heading)
                <h1 class="mt-3 text-4xl leading-[1.1] font-semibold sm:text-5xl">{{ $heading }}</h1>
            @endif
            @if ($subheading)
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/75 sm:text-lg">{{ $subheading }}</p>
            @endif
            {{ $slot }}
        </div>
    </div>
</section>
