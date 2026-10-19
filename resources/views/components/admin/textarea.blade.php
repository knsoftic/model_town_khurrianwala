@props(['name', 'label' => null, 'value' => null, 'rows' => 4, 'help' => null, 'required' => false, 'mono' => false])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id', 'f-'.str_replace('.', '-', $key));
    $error = $errors->first($key);
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="a-label">{{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif</label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($required) required @endif
              {{ $attributes->except(['class', 'id'])->class(['a-input', 'font-mono text-xs' => $mono, 'border-rose-400' => $error]) }}>{{ old($key, $value) }}</textarea>
    @if ($error)
        <p class="a-error">{{ $error }}</p>
    @elseif ($help)
        <p class="a-help">{{ $help }}</p>
    @endif
</div>
