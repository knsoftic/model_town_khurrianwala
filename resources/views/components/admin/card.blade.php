@props(['title' => null, 'description' => null, 'padding' => true])

<section {{ $attributes->class(['a-card']) }}>
    @if ($title || isset($actions))
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
            <div>
                @if ($title)
                    <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-0.5 text-xs text-slate-500">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</section>
