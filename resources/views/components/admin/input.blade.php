@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'help' => null, 'required' => false, 'prefix' => null, 'suffix' => null])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id', 'f-'.str_replace('.', '-', $key));
    $current = $type === 'password' ? null : old($key, $value);
    $error = $errors->first($key);
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="a-label">{{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif</label>
    @endif
    <div @class(['flex rounded-lg shadow-xs' => $prefix || $suffix])>
        @if ($prefix)
            <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">{{ $prefix }}</span>
        @endif
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}" @if ($required) required @endif
               {{ $attributes->except(['class', 'id'])->class(['a-input', 'rounded-l-none' => $prefix, 'rounded-r-none' => $suffix, 'border-rose-400' => $error]) }}>
        @if ($suffix)
            <span class="inline-flex items-center rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">{{ $suffix }}</span>
        @endif
    </div>
    @if ($error)
        <p class="a-error">{{ $error }}</p>
    @elseif ($help)
        <p class="a-help">{{ $help }}</p>
    @endif
</div>
