@extends('layouts.front')

@section('content')
    <x-front.page-banner :page="$page" :crumbs="[$page->name => null]" />

    <section class="py-16 sm:py-24">
        <div @class(['container-x grid items-start gap-12', 'lg:grid-cols-2 lg:gap-16' => $page->image])>
            @if ($page->image)
                <div class="relative lg:sticky lg:top-28">
                    <div class="absolute -right-4 -bottom-4 hidden size-40 rounded-brand-lg border-2 border-accent/40 sm:block"></div>
                    <img {{ img_attrs($page->image, '(min-width: 1024px) 50vw, 100vw', [480, 768, 960, 1280]) }} alt="{{ $page->heading }}" class="relative aspect-[4/3] w-full rounded-brand-lg object-cover shadow-lift">
                </div>
            @endif
            <div>
                @if ($page->content)
                    <h2 class="sr-only">{{ $page->heading ?: $page->name }}</h2>
                    <div class="prose-brand text-base">{!! $page->content !!}</div>
                @endif
                @if ($page->cta_text)
                    <a href="{{ url($page->cta_url ?: '/plots') }}" class="btn-primary mt-8 !px-7">{{ $page->cta_text }} <x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
        </div>

        @if (! empty($page->images))
            <div class="container-x mt-14 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($page->images as $image)
                    <img {{ img_attrs($image, '(min-width: 1024px) 25vw, 50vw', [320, 480, 640]) }} alt="" loading="lazy" class="aspect-square w-full rounded-brand-lg object-cover">
                @endforeach
            </div>
        @endif
    </section>

    @if ($page->setting('show_statistics', true) && $statistics->isNotEmpty())
        <section data-defer class="relative overflow-hidden bg-primary py-16 text-white">
            <div class="pointer-events-none absolute inset-0 bg-grid-light"></div>
            <dl class="relative container-x grid grid-cols-2 gap-8 lg:grid-cols-4">
                @foreach ($statistics as $stat)
                    <div class="text-center">
                        @if ($stat->icon)
                            <x-icon :name="$stat->icon" class="mx-auto size-8 text-accent" />
                        @endif
                        <dd class="mt-3 font-heading text-4xl font-semibold sm:text-5xl">{{ $stat->prefix }}<span @if ($stat->numericValue()) x-count-up data-value="{{ $stat->numericValue() }}" @endif>{{ $stat->value }}</span>{{ $stat->suffix }}</dd>
                        <dt class="mt-2 text-sm text-white/65">{{ $stat->label }}</dt>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    @if ($video || $page->video_url)
        <section data-defer class="py-16 sm:py-24">
            <div class="container-x">
                <x-front.section-heading eyebrow="Discover Our Project" :heading="$video?->title ?? 'Project Video'" />
                @if ($page->video_url && \App\Support\VideoEmbed::embedUrl($page->video_url))
                    <div class="mx-auto mt-10 max-w-5xl overflow-hidden rounded-brand-lg shadow-lift">
                        <iframe src="{{ \App\Support\VideoEmbed::embedUrl($page->video_url) }}" class="aspect-video w-full" loading="lazy" allowfullscreen title="Project video"></iframe>
                    </div>
                @elseif ($video)
                    <x-front.video-player :video="$video" class="mx-auto mt-10 max-w-5xl" />
                    @include('front.partials.video-modal')
                @endif
            </div>
        </section>
    @endif

    @if ($features->isNotEmpty())
        <section data-defer class="bg-white py-16 sm:py-24">
            <div class="container-x">
                <x-front.section-heading eyebrow="Project Features" heading="Planned for Modern Living" />
                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($features as $feature)
                        <x-front.feature-card :feature="$feature" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @include('front.partials.cta-band')
@endsection
