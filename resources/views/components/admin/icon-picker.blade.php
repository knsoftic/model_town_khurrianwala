@props(['name' => 'icon', 'label' => 'Icon', 'value' => null])

@php
    $current = old($name, $value);
    $icons = collect(\App\Support\Icons::CURATED)->mapWithKeys(fn ($icon) => [$icon => \App\Support\Icons::svg($icon)]);
@endphp

<div x-data="{ icon: @js($current), open: false, icons: @js($icons) }" class="relative" @click.outside="open = false">
    <label for="f-{{ $name }}" class="a-label">{{ $label }}</label>
    <div class="flex gap-2">
        <button type="button" @click="open = !open" class="grid size-10 shrink-0 place-items-center rounded-lg border border-slate-300 bg-white text-navy-900 shadow-xs hover:border-gold-500" aria-label="Choose icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-5" x-html="icons[icon] ?? ''"></svg>
        </button>
        <input id="f-{{ $name }}" type="text" name="{{ $name }}" x-model="icon" placeholder="e.g. shield-check" class="a-input">
    </div>
    <p class="a-help">Pick a suggestion or type any <a href="https://lucide.dev/icons" target="_blank" rel="noopener" class="underline">Lucide icon name</a>.</p>
    @error($name)<p class="a-error">{{ $message }}</p>@enderror
    <div x-cloak x-show="open" x-transition class="absolute z-20 mt-2 grid w-80 grid-cols-8 gap-1 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
        <template x-for="(svg, key) in icons" :key="key">
            <button type="button" @click="icon = key; open = false" :title="key" :class="icon === key ? 'bg-gold-100 text-navy-900' : 'text-slate-600 hover:bg-slate-100'" class="grid size-9 place-items-center rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-5" x-html="svg"></svg>
            </button>
        </template>
    </div>
</div>
