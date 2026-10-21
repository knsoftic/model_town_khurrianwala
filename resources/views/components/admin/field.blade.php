@props(['name', 'field', 'value' => null, 'fileName' => null])

{{--
    Renders one field from a definition array (config/cms.php settings or App\Support\SectionFields):
    type = text|textarea|email|url|tel|number|color|boolean|select|image|video|rich|code|password|keywords.
    Image/video uploads post under $fileName (defaults to $name) with a "remove_<fileName>" flag.
--}}
@php
    $type = $field['type'] ?? 'text';
    $label = $field['label'] ?? str($name)->afterLast('[')->rtrim(']')->headline();
    $help = $field['help'] ?? null;
    $fileName ??= $name;
    $span = in_array($type, ['textarea', 'code', 'rich', 'image', 'video', 'keywords'], true) ? 'sm:col-span-2' : '';
@endphp

@switch($type)
    @case('keywords')
        <x-admin.keywords :name="$name" :label="$label" :value="$value" :help="$help" :class="$span" />
        @break

    @case('textarea')
        <x-admin.textarea :name="$name" :label="$label" :value="$value" :help="$help" rows="3" :class="$span" />
        @break

    @case('code')
        <x-admin.textarea :name="$name" :label="$label" :value="$value" :help="$help" rows="8" :mono="true" :class="$span" spellcheck="false" />
        @break

    @case('rich')
        <x-admin.rich :name="$name" :label="$label" :value="$value" :help="$help" :class="$span" />
        @break

    @case('color')
        <x-admin.color :name="$name" :label="$label" :value="$value" :help="$help" />
        @break

    @case('boolean')
        <div class="flex items-center sm:pt-6">
            <x-admin.toggle :name="$name" :label="$label" :checked="filter_var($value, FILTER_VALIDATE_BOOLEAN)" :help="$help" />
        </div>
        @break

    @case('select')
        <x-admin.select :name="$name" :label="$label" :value="$value" :options="$field['options'] ?? []" :help="$help" />
        @break

    @case('image')
        <x-admin.image :name="$fileName" :label="$label" :current="$value" :help="$help" :class="$span"
                       :accept="($field['mimes'] ?? null) === 'png,ico' ? 'image/png,image/x-icon,.ico' : 'image/jpeg,image/png,image/webp'" />
        @break

    @case('video')
        @php($removeName = 'remove_'.str_replace(['[', ']'], ['_', ''], $fileName))
        <div x-data="{ removed: false }" class="{{ $span }}">
            <span class="a-label">{{ $label }}</span>
            @if ($value)
                <div class="mb-2 flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2 text-sm" x-show="!removed">
                    <x-icon name="clapperboard" class="size-4 text-slate-500" />
                    <a href="{{ media_url($value) }}" target="_blank" class="truncate text-sky-700 underline">{{ basename($value) }}</a>
                    <button type="button" class="ml-auto text-xs font-semibold text-rose-600" @click="removed = true">Remove</button>
                </div>
            @endif
            <input type="hidden" name="{{ $removeName }}" :value="removed ? 1 : 0">
            <input type="file" name="{{ $fileName }}" accept="video/mp4,video/webm" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold hover:file:bg-slate-200">
            <p class="a-help">{{ $help ?? 'MP4 or WebM, max '.round(config('cms.uploads.video_max_kb') / 1024).' MB.' }}</p>
            @error(str_replace(['[', ']'], ['.', ''], $fileName))<p class="a-error">{{ $message }}</p>@enderror
        </div>
        @break

    @case('password')
        <x-admin.input :name="$name" type="password" :label="$label" :help="$help" autocomplete="new-password"
                       :placeholder="filled($value) ? '•••••••• (saved — leave blank to keep)' : ''" />
        @break

    @default
        <x-admin.input :name="$name" :type="$type" :label="$label" :value="$value" :help="$help" />
@endswitch
