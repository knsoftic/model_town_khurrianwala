@props(['testimonial'])

<figure {{ $attributes->class(['relative flex h-full flex-col rounded-brand-lg border border-primary/8 bg-white p-7 shadow-soft']) }}>
    <x-icon name="quote" class="absolute top-6 right-6 size-10 text-accent/25" stroke="1.25" />

    <div class="flex gap-0.5 text-accent" aria-label="{{ $testimonial->rating }} out of 5 stars">
        @for ($i = 1; $i <= 5; $i++)
            <svg viewBox="0 0 20 20" @class(['size-4.5', 'fill-current' => $i <= $testimonial->rating, 'fill-primary/10' => $i > $testimonial->rating]) aria-hidden="true">
                <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
            </svg>
        @endfor
    </div>

    <blockquote class="mt-5 flex-1 text-[15px] leading-relaxed text-ink/80">“{{ $testimonial->review }}”</blockquote>

    @if ($testimonial->videoEmbedUrl())
        <button type="button" @click="$dispatch('play-video', { src: @js($testimonial->videoEmbedUrl().'&autoplay=1') })"
                class="mt-5 inline-flex items-center gap-2 self-start rounded-full bg-primary/5 px-3 py-1.5 text-xs font-semibold text-primary transition hover:bg-accent">
            <x-icon name="circle-play" class="size-4" /> Watch video review
        </button>
    @endif

    <figcaption class="mt-6 flex items-center gap-3.5 border-t border-primary/8 pt-5">
        @if ($testimonial->client_image)
            <img src="{{ media_url($testimonial->client_image) }}" alt="{{ $testimonial->client_name }}" loading="lazy" class="size-12 rounded-full object-cover ring-2 ring-accent/40">
        @else
            <span class="grid size-12 shrink-0 place-items-center rounded-full bg-primary font-heading text-base font-semibold text-accent">{{ $testimonial->initials() }}</span>
        @endif
        <div class="min-w-0">
            <p class="font-semibold text-primary">{{ $testimonial->client_name }}</p>
            <p class="truncate text-xs text-ink/55">
                {{ collect([$testimonial->client_title, $testimonial->location])->filter()->implode(' · ') }}
            </p>
        </div>
        @if ($testimonial->plot)
            <span class="ml-auto shrink-0 rounded-full bg-accent/12 px-2.5 py-1 text-[11px] font-semibold text-primary">{{ $testimonial->plot->size_label }}</span>
        @endif
    </figcaption>
</figure>
