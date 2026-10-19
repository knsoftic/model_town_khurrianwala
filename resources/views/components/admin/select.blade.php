@props(['name', 'label' => null, 'value' => null, 'options' => [], 'placeholder' => null, 'help' => null, 'required' => false])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = $attributes->get('id', 'f-'.str_replace('.', '-', $key));
    $current = old($key, $value instanceof \BackedEnum ? $value->value : $value);
    $error = $errors->first($key);
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $id }}" class="a-label">{{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif</label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif {{ $attributes->except(['class', 'id'])->class(['a-input', 'border-rose-400' => $error]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($error)
        <p class="a-error">{{ $error }}</p>
    @elseif ($help)
        <p class="a-help">{{ $help }}</p>
    @endif
</div>
