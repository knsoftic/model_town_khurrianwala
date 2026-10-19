@props(['name', 'label', 'value' => null, 'help' => null])

@php
    $id = 'f-'.$name;
    $current = old($name, $value) ?: '#000000';
    $error = $errors->first($name);
@endphp

<div x-data="{ color: @js($current) }" {{ $attributes->class([]) }}>
    <label for="{{ $id }}" class="a-label">{{ $label }}</label>
    <div class="flex items-center gap-2">
        <input type="color" x-model="color" class="h-10 w-12 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1" aria-label="{{ $label }} picker">
        <input id="{{ $id }}" type="text" name="{{ $name }}" x-model="color" maxlength="7" pattern="#[0-9A-Fa-f]{6}" class="a-input font-mono uppercase">
    </div>
    @if ($error)
        <p class="a-error">{{ $error }}</p>
    @elseif ($help)
        <p class="a-help">{{ $help }}</p>
    @endif
</div>
