@props(['url' => null, 'light' => true])

@if ($url)
    <img src="{{ $url }}" alt="{{ setting('site_name') }}" {{ $attributes->merge(['class' => 'h-11 w-auto object-contain']) }}>
@else
    <span {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
        <span class="grid size-11 shrink-0 place-items-center rounded-brand-sm bg-accent text-primary shadow-sm ring-1 ring-white/20">
            <x-icon name="land-plot" class="size-6" stroke="2" />
        </span>
        <span class="leading-tight">
            <span @class(['block font-heading text-lg font-semibold tracking-tight sm:text-xl', 'text-current' => $light, 'text-primary' => ! $light])>{{ setting('site_name') }}</span>
            @if (setting('logo_subtitle'))
                <span class="block text-[10px] font-medium tracking-[0.24em] uppercase opacity-70">{{ setting('logo_subtitle') }}</span>
            @endif
        </span>
    </span>
@endif
