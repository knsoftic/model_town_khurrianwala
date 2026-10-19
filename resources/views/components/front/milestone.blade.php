@props(['milestone', 'detailed' => false])

<div x-reveal {{ $attributes->class(['card p-5 sm:p-6']) }}>
    <div class="flex items-start gap-4">
        <span class="grid size-12 shrink-0 place-items-center rounded-brand bg-accent/12 text-accent">
            <x-icon :name="$milestone->icon ?: 'hard-hat'" class="size-6" />
        </span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="font-sans text-base font-semibold text-primary sm:text-lg">{{ $milestone->title }}</h3>
                <div class="flex items-center gap-3">
                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $milestone->status->badgeClasses() }}">{{ $milestone->status->label() }}</span>
                    <span class="font-heading text-xl font-semibold text-primary">{{ $milestone->percentage }}%</span>
                </div>
            </div>
            @if ($milestone->description)
                <p class="mt-1 text-sm text-ink/60">{{ $milestone->description }}</p>
            @endif
            <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-primary/8" role="progressbar" aria-valuenow="{{ $milestone->percentage }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $milestone->title }}">
                <div class="h-full rounded-full bg-gradient-to-r from-accent/80 to-accent transition-[width] duration-[1600ms] ease-out"
                     style="width: calc({{ $milestone->percentage }}% * var(--reveal, 1))"></div>
            </div>
        </div>
    </div>
    @if ($detailed && $milestone->image)
        <img src="{{ media_url($milestone->image) }}" alt="{{ $milestone->title }}" loading="lazy" class="mt-5 aspect-[16/7] w-full rounded-brand object-cover">
    @endif
</div>
