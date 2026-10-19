@props(['name', 'label' => null, 'value' => null, 'help' => null, 'minHeight' => 'min-h-48'])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $error = $errors->first($key);
    $tools = [
        ['bold', 'bold', 'Bold'], ['italic', 'italic', 'Italic'], ['underline', 'underline', 'Underline'],
    ];
@endphp

<div x-data="richEditor" {{ $attributes->class([]) }}>
    @if ($label)
        <span class="a-label">{{ $label }}</span>
    @endif
    <div @class(['overflow-hidden rounded-lg border bg-white shadow-xs focus-within:border-gold-500 focus-within:ring-2 focus-within:ring-gold-500/30', 'border-rose-400' => $error, 'border-slate-300' => ! $error])>
        <div class="flex flex-wrap items-center gap-0.5 border-b border-slate-200 bg-slate-50 px-2 py-1.5">
            @foreach ($tools as [$command, $icon, $title])
                <button type="button" class="a-icon-btn" title="{{ $title }}" @click="exec('{{ $command }}')"><x-icon :name="$icon" class="size-4" /></button>
            @endforeach
            <span class="mx-1 h-5 w-px bg-slate-300"></span>
            <button type="button" class="a-icon-btn" title="Heading" @click="block('h3')"><x-icon name="heading-2" class="size-4" /></button>
            <button type="button" class="a-icon-btn" title="Paragraph" @click="block('p')"><x-icon name="pilcrow" class="size-4" /></button>
            <button type="button" class="a-icon-btn" title="Bulleted list" @click="exec('insertUnorderedList')"><x-icon name="list" class="size-4" /></button>
            <button type="button" class="a-icon-btn" title="Numbered list" @click="exec('insertOrderedList')"><x-icon name="list-ordered" class="size-4" /></button>
            <button type="button" class="a-icon-btn" title="Quote" @click="block('blockquote')"><x-icon name="text-quote" class="size-4" /></button>
            <button type="button" class="a-icon-btn" title="Link" @click="link()"><x-icon name="link" class="size-4" /></button>
            <button type="button" class="a-icon-btn" title="Clear formatting" @click="exec('removeFormat')"><x-icon name="remove-formatting" class="size-4" /></button>
        </div>
        <div x-ref="editor" contenteditable="true" @input="sync()" @blur="sync()" @paste="paste"
             class="rich-content {{ $minHeight }} max-h-[32rem] overflow-y-auto px-4 py-3 text-sm leading-relaxed text-slate-800 outline-none"></div>
    </div>
    <textarea x-ref="input" name="{{ $name }}" class="hidden">{{ old($key, $value) }}</textarea>
    @if ($error)
        <p class="a-error">{{ $error }}</p>
    @elseif ($help)
        <p class="a-help">{{ $help }}</p>
    @endif
</div>
