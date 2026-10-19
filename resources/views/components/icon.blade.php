@props(['name' => null, 'stroke' => 1.75])
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" {{ $attributes->merge(['class' => 'shrink-0']) }}>{!! \App\Support\Icons::svg($name) !!}</svg>
