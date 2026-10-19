@props(['column', 'label'])

@php
    $current = request('sort');
    $direction = request('direction', 'asc');
    $isActive = $current === $column;
    $next = $isActive && $direction === 'asc' ? 'desc' : 'asc';
@endphp

<a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]) }}" class="inline-flex items-center gap-1 hover:text-slate-900">
    {{ $label }}
    @if ($isActive)
        <x-icon :name="$direction === 'asc' ? 'arrow-up' : 'arrow-down'" class="size-3" />
    @else
        <x-icon name="arrow-up-down" class="size-3 opacity-40" />
    @endif
</a>
