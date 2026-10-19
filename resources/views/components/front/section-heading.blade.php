@props(['eyebrow' => null, 'heading' => null, 'subheading' => null, 'align' => 'center', 'dark' => false])

@if ($eyebrow || $heading || $subheading)
    <div {{ $attributes->class([
        'max-w-3xl',
        'mx-auto text-center' => $align === 'center',
        'text-left' => $align === 'left',
    ]) }}>
        @if ($eyebrow)
            <span @class(['eyebrow', 'justify-center' => $align === 'center'])>{{ $eyebrow }}</span>
        @endif
        @if ($heading)
            <h2 @class(['mt-3 text-3xl leading-tight font-semibold sm:text-4xl lg:text-[2.75rem]', 'text-white' => $dark, 'text-primary' => ! $dark])>{{ $heading }}</h2>
        @endif
        @if ($subheading)
            <p @class(['mt-4 text-base leading-relaxed sm:text-lg', 'text-white/70' => $dark, 'text-ink/70' => ! $dark])>{{ $subheading }}</p>
        @endif
    </div>
@endif
