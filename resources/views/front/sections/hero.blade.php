@php
    $type = $section->setting('background_type', 'image');
    $imagePath = $section->setting('background_image');
    $image = img_url($imagePath, 1280);
    $video = media_url($section->setting('background_video')) ?? $section->setting('background_video_url');
    $overlay = max(0, min(95, (int) $section->setting('overlay_opacity', 55))) / 100;
    $height = max(50, min(100, (int) $section->setting('height', 92)));
    $align = $section->setting('text_alignment', 'left');
    $overlap = $first && setting_enabled('header_transparent_home');
    $showFacts = $section->setting('show_quick_facts', true) && isset($plots) && $plots->isNotEmpty();
    $sideCard = $showFacts && $align === 'left';
@endphp

<section class="relative isolate flex items-center overflow-hidden bg-primary text-white"
         style="min-height: max(620px, {{ $height }}svh); {{ $overlap ? 'margin-top: calc(var(--chrome-h) * -1); padding-top: var(--chrome-h);' : '' }}">
    @if ($type === 'video' && $video)
        <video class="absolute inset-0 -z-20 size-full object-cover" autoplay muted loop playsinline @if ($image) poster="{{ $image }}" @endif>
            <source src="{{ $video }}">
        </video>
    @elseif ($image && $type !== 'gradient')
        @if ($first)
            @push('head'){{ img_preload($imagePath, '100vw', [640, 960, 1280, 1920]) }}@endpush
        @endif
        <img {{ img_attrs($imagePath, '100vw', [640, 960, 1280, 1920]) }} alt="" class="absolute inset-0 -z-20 size-full object-cover" fetchpriority="high">
    @endif
    <div class="absolute inset-0 -z-10 bg-primary" style="opacity: {{ $overlay }}"></div>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-primary via-transparent to-primary/40"></div>
    <div class="pointer-events-none absolute -right-40 -bottom-40 -z-10 size-[620px] rounded-full bg-accent/15 blur-3xl"></div>

    <div @class([
        'container-x relative grid items-center gap-12 py-16 lg:py-24',
        'lg:grid-cols-[1.15fr_0.85fr]' => $sideCard,
    ])>
        <div @class([
            'max-w-3xl',
            'mx-auto text-center' => $align === 'center',
            'ml-auto text-right' => $align === 'right',
        ])>
            @if ($section->setting('badge_text'))
                <span class="inline-flex animate-rise items-center gap-2 rounded-full border border-accent/40 bg-accent/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-accent backdrop-blur">
                    <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-accent opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-accent"></span></span>
                    {{ $section->setting('badge_text') }}
                </span>
            @endif

            @if ($section->heading)
                <h1 class="mt-6 animate-rise text-4xl leading-[1.05] font-semibold [animation-delay:80ms] sm:text-5xl lg:text-6xl xl:text-[4.25rem]">
                    {{ $section->heading }}
                </h1>
            @endif

            @if ($section->subheading)
                <p class="mt-6 animate-rise text-lg font-medium text-accent [animation-delay:160ms] sm:text-xl">{{ $section->subheading }}</p>
            @endif

            @if ($section->description)
                <p @class(['mt-4 max-w-2xl animate-rise text-base leading-relaxed text-white/75 [animation-delay:220ms] sm:text-lg', 'mx-auto' => $align === 'center', 'ml-auto' => $align === 'right'])>{{ $section->description }}</p>
            @endif

            <div @class(['mt-9 flex animate-rise flex-wrap gap-3 [animation-delay:300ms]', 'justify-center' => $align === 'center', 'justify-end' => $align === 'right'])>
                @if ($section->setting('primary_button_text'))
                    <a href="{{ url($section->setting('primary_button_url', '/plots')) }}" class="btn-primary !px-7 !py-4 text-[15px]">
                        {{ $section->setting('primary_button_text') }} <x-icon name="arrow-right" class="size-4" />
                    </a>
                @endif
                @if ($section->setting('secondary_button_text'))
                    <a href="{{ url($section->setting('secondary_button_url', '/book-plot')) }}" class="btn-ghost-light !px-7 !py-4 text-[15px]">
                        {{ $section->setting('secondary_button_text') }}
                    </a>
                @endif
            </div>

            @if ($showFacts && ! $sideCard)
                <div @class(['mt-12 flex flex-wrap gap-3', 'justify-center' => $align === 'center', 'justify-end' => $align === 'right'])>
                    @foreach ($plots->take(4) as $plot)
                        <a href="{{ route('plots.show', $plot) }}" class="rounded-brand border border-white/15 bg-white/5 px-4 py-3 text-left backdrop-blur transition hover:border-accent/60 hover:bg-white/10">
                            <span class="block text-sm font-semibold">{{ $plot->size_label }}</span>
                            <span class="block text-xs text-white/65">from {{ money($plot->installment_amount ?? $plot->displayPrice()) }}{{ $plot->installment_amount ? ' '.$plot->frequencySuffix() : '' }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($sideCard)
            <div class="animate-fade-up [animation-delay:380ms]">
                <div class="rounded-brand-lg border border-white/15 bg-white/[0.07] p-6 shadow-2xl backdrop-blur-md sm:p-7">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold tracking-wide text-white">Plot Sizes &amp; Installments</p>
                        <span class="rounded-full bg-accent/15 px-2.5 py-1 text-[11px] font-semibold text-accent">{{ $plots->count() }} options</span>
                    </div>
                    <ul class="mt-5 space-y-2.5">
                        @foreach ($plots->take(5) as $plot)
                            <li>
                                <a href="{{ route('plots.show', $plot) }}" class="group flex items-center gap-4 rounded-brand bg-white/5 px-4 py-3.5 ring-1 ring-white/10 transition hover:bg-white/10 hover:ring-accent/50">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-brand-sm bg-accent/15 text-accent"><x-icon name="land-plot" class="size-5" /></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center gap-2">
                                            <span class="font-semibold">{{ $plot->size_label }}</span>
                                            <x-front.status-badge :status="$plot->status" class="!px-2 !py-0.5 !text-[9px] bg-white!" />
                                        </span>
                                        <span class="mt-0.5 block text-xs text-white/60">@if ($plot->booking_amount)Booking {{ money($plot->booking_amount) }}@else{{ $plot->stageTitle(['advance', 'full_payment'], 'Down Payment') }} {{ money($plot->down_payment) }}@endif</span>
                                    </span>
                                    <span class="text-right">
                                        <span class="block text-sm font-semibold text-accent">{{ money($plot->installment_amount) }}</span>
                                        <span class="block text-[11px] text-white/55">{{ $plot->frequencySuffix() }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <a href="#calculator" class="mt-5 flex items-center justify-center gap-2 rounded-brand border border-dashed border-white/25 py-3 text-sm font-semibold text-white/85 transition hover:border-accent hover:text-accent">
                        <x-icon name="calculator" class="size-4" /> Calculate your payment plan
                    </a>
                </div>
            </div>
        @endif
    </div>

    <a href="#{{ 'after-hero' }}" class="absolute bottom-6 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-[10px] font-semibold tracking-[0.3em] text-white/50 uppercase transition hover:text-accent md:flex" aria-label="Scroll down">
        Scroll <x-icon name="chevrons-down" class="size-4 animate-bounce" />
    </a>
</section>
<div id="after-hero"></div>
