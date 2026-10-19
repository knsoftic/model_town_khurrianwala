@props(['name', 'label' => null, 'current' => null, 'help' => null, 'accept' => 'image/jpeg,image/png,image/webp', 'aspect' => 'aspect-video'])

@php
    $key = str_replace(['[', ']'], ['.', ''], $name);
    $id = 'f-'.str_replace('.', '-', $key);
    $error = $errors->first($key);
    $removeName = 'remove_'.str_replace(['[', ']'], ['_', ''], $name);
@endphp

<div x-data="imageInput(@js(media_url($current)))" {{ $attributes->class([]) }}>
    @if ($label)
        <span class="a-label">{{ $label }}</span>
    @endif
    <div class="flex items-start gap-4">
        <div @class(['relative w-40 shrink-0 overflow-hidden rounded-lg border border-dashed border-slate-300 bg-slate-50', $aspect])>
            <template x-if="preview">
                <img :src="preview" alt="" class="absolute inset-0 size-full object-contain">
            </template>
            <div x-show="!preview" class="absolute inset-0 grid place-items-center text-slate-400"><x-icon name="image" class="size-7" /></div>
        </div>
        <div class="min-w-0 space-y-2">
            <label for="{{ $id }}" class="a-btn-secondary a-btn-sm cursor-pointer"><x-icon name="upload" class="size-3.5" /> Choose file</label>
            <input id="{{ $id }}" x-ref="file" type="file" name="{{ $name }}" accept="{{ $accept }}" class="sr-only" @change="pick">
            <button type="button" x-show="preview" @click="clear()" class="a-btn-ghost a-btn-sm text-rose-600"><x-icon name="trash" class="size-3.5" /> Remove</button>
            <input type="hidden" name="{{ $removeName }}" :value="removed ? 1 : 0">
            @if ($help)
                <p class="a-help !mt-0">{{ $help }}</p>
            @else
                <p class="a-help !mt-0">JPG, PNG or WebP · max {{ round(config('cms.uploads.image_max_kb') / 1024) }} MB</p>
            @endif
        </div>
    </div>
    @if ($error)
        <p class="a-error">{{ $error }}</p>
    @endif
</div>
