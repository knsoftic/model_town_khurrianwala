@props(['feature'])

<div {{ $attributes->class(['group relative overflow-hidden rounded-brand-lg border border-primary/8 bg-surface/60 p-6 transition duration-300 hover:-translate-y-0.5 hover:border-accent/40 hover:bg-white hover:shadow-lift']) }}>
    @if ($feature->image)
        <img {{ img_attrs($feature->image, '(min-width: 1280px) 25vw, (min-width: 640px) 50vw, 100vw', [320, 480, 640, 960]) }} alt="{{ $feature->title }}" loading="lazy" class="mb-5 aspect-[16/9] w-full rounded-brand object-cover">
    @else
        <span class="grid size-14 place-items-center rounded-brand bg-primary text-accent transition duration-300 group-hover:bg-accent group-hover:text-primary">
            <x-icon :name="$feature->icon ?: 'circle-check'" class="size-7" stroke="1.6" />
        </span>
    @endif
    <h3 class="mt-5 font-sans text-lg font-semibold text-primary">{{ $feature->title }}</h3>
    @if ($feature->description)
        <p class="mt-2 text-sm leading-relaxed text-ink/70">{{ $feature->description }}</p>
    @endif
</div>
