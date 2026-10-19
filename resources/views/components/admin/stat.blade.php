@props(['label', 'value', 'icon' => 'circle', 'hint' => null, 'tone' => 'navy', 'href' => null])

@php
    $tones = [
        'navy' => 'bg-navy-900 text-gold-400',
        'gold' => 'bg-gold-100 text-gold-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'rose' => 'bg-rose-100 text-rose-700',
        'violet' => 'bg-violet-100 text-violet-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'slate' => 'bg-slate-100 text-slate-700',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['a-card flex items-center gap-4 p-5', 'transition hover:-translate-y-0.5 hover:shadow-md' => $href]) }}>
    <span class="grid size-12 shrink-0 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['navy'] }}"><x-icon :name="$icon" class="size-6" /></span>
    <div class="min-w-0">
        <p class="truncate text-xs font-medium text-slate-500">{{ $label }}</p>
        <p class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
        @if ($hint)
            <p class="truncate text-[11px] text-slate-400">{{ $hint }}</p>
        @endif
    </div>
</{{ $tag }}>
