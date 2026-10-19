@php
    $image = $section->setting('image');
    $stats = $section->setting('show_statistics', true) ? ($statistics ?? collect()) : collect();
@endphp

<section class="relative overflow-hidden py-20 sm:py-28">
    <div class="container-x grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
        <div class="relative" x-reveal>
            <div class="absolute -top-5 -left-5 hidden size-40 rounded-brand-lg border-2 border-accent/40 sm:block"></div>
            <div class="relative overflow-hidden rounded-brand-lg shadow-lift">
                @if ($image)
                    <img {{ img_attrs($image, '(min-width: 1024px) 50vw, 100vw', [480, 768, 960, 1280]) }} alt="{{ $section->heading }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                @else
                    <div class="aspect-[4/3] w-full bg-primary bg-grid-light"></div>
                @endif
            </div>
            @if ($stats->isNotEmpty())
                @php($lead = $stats->first())
                <div class="absolute -right-3 -bottom-8 rounded-brand-lg bg-primary p-5 text-white shadow-lift sm:-right-8 sm:p-6">
                    <p class="font-heading text-4xl font-semibold text-accent">{{ $lead->prefix }}<span x-count-up data-value="{{ $lead->numericValue() }}">{{ $lead->value }}</span>{{ $lead->suffix }}</p>
                    <p class="mt-1 text-sm text-white/70">{{ $lead->label }}</p>
                </div>
            @endif
        </div>

        <div x-reveal>
            <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" align="left" />
            @if ($section->description)
                <p class="mt-6 text-base leading-relaxed text-ink/70 sm:text-lg">{{ $section->description }}</p>
            @endif

            @if ($stats->isNotEmpty())
                <dl class="mt-10 grid grid-cols-2 gap-4">
                    @foreach ($stats as $stat)
                        <div class="flex flex-col rounded-brand border border-primary/8 bg-white p-5 shadow-soft">
                            <dt class="order-2 mt-1 text-sm text-ink/70">{{ $stat->label }}</dt>
                            <dd class="order-1">
                                @if ($stat->icon)
                                    <x-icon :name="$stat->icon" class="size-6 text-accent" />
                                @endif
                                <span class="mt-3 block font-heading text-3xl font-semibold text-primary">
                                    {{ $stat->prefix }}<span @if ($stat->numericValue()) x-count-up data-value="{{ $stat->numericValue() }}" @endif>{{ $stat->value }}</span>{{ $stat->suffix }}
                                </span>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endif

            @if ($section->setting('button_text'))
                <a href="{{ url($section->setting('button_url', '/about')) }}" class="btn-dark mt-10 !px-7 !py-3.5">
                    {{ $section->setting('button_text') }} <x-icon name="arrow-right" class="size-4" />
                </a>
            @endif
        </div>
    </div>
</section>
