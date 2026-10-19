@php
    $image = media_url($section->setting('image'));
    $layout = $section->setting('layout', 'image_right');
    $dark = $section->setting('background') === 'dark';
@endphp

<section @class(['relative py-20 sm:py-28', 'bg-primary text-white' => $dark, 'bg-white' => $section->setting('background') === 'white'])>
    <div @class(['container-x grid items-center gap-12', 'lg:grid-cols-2 lg:gap-16' => $image && $layout !== 'text_only'])>
        <div @class(['lg:order-2' => $image && $layout === 'image_left'])>
            <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" :align="$image ? 'left' : 'center'" :dark="$dark" />
            @if ($section->setting('content'))
                <div @class(['prose-brand mt-6', 'text-white/80 [&_strong]:text-white [&_h3]:text-white' => $dark, 'mx-auto max-w-3xl text-center' => ! $image])>{!! $section->setting('content') !!}</div>
            @endif
            @if ($section->setting('button_text'))
                <div @class(['mt-8', 'text-center' => ! $image])>
                    <a href="{{ url($section->setting('button_url', '/')) }}" class="btn-primary !px-7">{{ $section->setting('button_text') }} <x-icon name="arrow-right" class="size-4" /></a>
                </div>
            @endif
        </div>
        @if ($image && $layout !== 'text_only')
            <img src="{{ $image }}" alt="{{ $section->heading }}" loading="lazy" @class(['w-full rounded-brand-lg object-cover shadow-lift', 'lg:order-1' => $layout === 'image_left'])>
        @endif
    </div>
</section>
