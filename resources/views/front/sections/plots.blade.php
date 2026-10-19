@php
    $list = ($plots ?? collect())
        ->when($section->setting('featured_only'), fn ($items) => $items->where('is_featured', true))
        ->take((int) $section->setting('limit', 8));
@endphp

@if ($list->isNotEmpty())
    <section id="plot-sizes" class="relative bg-white py-20 sm:py-28">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-full bg-grid-dark opacity-60 [mask-image:linear-gradient(to_bottom,black,transparent_60%)]"></div>
        <div class="relative container-x">
            <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" />

            <div @class([
                'mt-14 grid gap-6 sm:grid-cols-2',
                'xl:grid-cols-4' => $list->count() >= 4,
                'lg:grid-cols-3' => $list->count() === 3,
            ])>
                @foreach ($list as $plot)
                    <x-front.plot-card :plot="$plot" x-reveal />
                @endforeach
            </div>

            @if ($section->setting('button_text'))
                <div class="mt-12 text-center">
                    <a href="{{ url($section->setting('button_url', '/plots')) }}" class="btn-outline !px-7">
                        {{ $section->setting('button_text') }} <x-icon name="arrow-right" class="size-4" />
                    </a>
                </div>
            @endif
        </div>
    </section>
@endif
