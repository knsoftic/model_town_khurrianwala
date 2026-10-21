@props(['name', 'label' => null, 'value' => null, 'help' => null, 'placeholder' => 'e.g. plots for sale in Faisalabad'])

{{-- Keyword list with an Add button. Posts one comma-separated value under $name. --}}
@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = 'f-'.str_replace('.', '-', $key);
    $current = (string) old($key, $value);
    $error = $errors->first($key);
@endphp

<div {{ $attributes->only('class') }} x-data="keywordInput(@js($current))">
    @if ($label)
        <label for="{{ $id }}" class="a-label">{{ $label }}</label>
    @endif
    <input type="hidden" name="{{ $name }}" value="{{ $current }}" :value="value">
    <div class="flex gap-2">
        <input id="{{ $id }}" type="text" x-ref="input" x-model="draft" @keydown.enter.prevent="add()" @keydown="$event.key === ',' && ($event.preventDefault(), add())"
               placeholder="{{ $placeholder }}" autocomplete="off" @class(['a-input', 'border-rose-400' => $error])>
        <button type="button" class="a-btn-secondary shrink-0" @click="add()"><x-icon name="plus" class="size-4" /> Add</button>
    </div>
    <div x-cloak x-show="keywords.length" class="mt-2.5 flex flex-wrap gap-1.5">
        <template x-for="(keyword, index) in keywords" :key="keyword">
            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 py-0.5 pr-0.5 pl-3 text-xs font-medium text-slate-700">
                <span x-text="keyword"></span>
                <button type="button" class="grid size-6 place-items-center rounded-full text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" @click="remove(index)" :aria-label="'Remove ' + keyword">
                    <x-icon name="x" class="size-3.5" />
                </button>
            </span>
        </template>
    </div>
    <p x-cloak x-show="keywords.length" class="mt-1.5 text-xs text-slate-500">
        <span x-text="keywords.length"></span> <span x-text="keywords.length === 1 ? 'keyword' : 'keywords'"></span> ·
        <button type="button" class="font-semibold text-rose-600 hover:underline" @click="keywords = []">Remove all</button>
    </p>
    @if ($error)
        <p class="a-error">{{ $error }}</p>
    @elseif ($help)
        <p class="a-help">{{ $help }}</p>
    @endif
</div>
