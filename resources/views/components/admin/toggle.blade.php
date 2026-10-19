@props(['name', 'label', 'checked' => false, 'help' => null])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = 'f-'.str_replace('.', '-', $key).'-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5));
    $isOn = (bool) old($key, $checked);
@endphp

<label for="{{ $id }}" {{ $attributes->class(['flex cursor-pointer items-start gap-3']) }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <span class="relative mt-0.5 inline-flex shrink-0">
        <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1" @checked($isOn) class="peer sr-only">
        <span class="h-5 w-9 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500 peer-focus-visible:ring-2 peer-focus-visible:ring-gold-500 peer-focus-visible:ring-offset-2"></span>
        <span class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white shadow transition peer-checked:translate-x-4"></span>
    </span>
    <span>
        <span class="block text-sm font-medium text-slate-700">{{ $label }}</span>
        @if ($help)
            <span class="block text-xs text-slate-500">{{ $help }}</span>
        @endif
    </span>
</label>
