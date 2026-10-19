@props(['images', 'albums' => null])

{{-- Masonry grid + category filters + lightbox with zoom. --}}
<div x-data="gallery" @keydown.window="if (open) { if ($event.key === 'Escape') close(); if ($event.key === 'ArrowRight') step(1); if ($event.key === 'ArrowLeft') step(-1); }">
    @if ($albums && $albums->count() > 1)
        <div class="scrollbar-none -mx-4 mb-10 flex gap-2 overflow-x-auto px-4 sm:flex-wrap sm:justify-center" role="group" aria-label="Gallery categories">
            <button type="button" @click="filter = 'all'" :aria-pressed="filter === 'all'" :class="filter === 'all' ? 'bg-primary text-white' : 'bg-white text-primary ring-1 ring-primary/10 hover:ring-accent'" class="shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold transition">All</button>
            @foreach ($albums as $album)
                <button type="button" @click="filter = @js($album->slug)" :aria-pressed="filter === @js($album->slug)" :class="filter === @js($album->slug) ? 'bg-primary text-white' : 'bg-white text-primary ring-1 ring-primary/10 hover:ring-accent'" class="shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold transition">
                    {{ $album->name }} <span class="ml-1 text-xs opacity-60">{{ $album->activeImages->count() }}</span>
                </button>
            @endforeach
        </div>
    @endif

    <div class="columns-1 gap-4 sm:columns-2 lg:columns-3 xl:columns-4 [&>*]:mb-4">
        @foreach ($images as $i => $image)
            <button type="button" data-gallery-item data-src="{{ img_url($image->path, 1920) }}" data-title="{{ $image->title }}" data-caption="{{ $image->caption }}" data-album="{{ $image->album?->slug }}"
                    x-show="visible(@js($image->album?->slug))" x-transition.opacity @click="show({{ $loop->index }})"
                    class="group relative block w-full break-inside-avoid overflow-hidden rounded-brand-lg bg-primary/5 text-left">
                <img {{ img_attrs($image->path, '(min-width: 1280px) 25vw, (min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw', [320, 480, 640, 960]) }} alt="{{ $image->title ?? $image->album?->name }}" loading="lazy" class="w-full transition duration-700 group-hover:scale-105">
                <span class="absolute inset-0 bg-gradient-to-t from-primary/80 via-primary/0 to-primary/0 opacity-0 transition duration-300 group-hover:opacity-100"></span>
                <span class="absolute inset-x-0 bottom-0 flex translate-y-2 items-end justify-between gap-3 p-4 text-white opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                    <span class="min-w-0">
                        <span class="block text-[11px] font-semibold tracking-wider text-accent uppercase">{{ $image->album?->name }}</span>
                        @if ($image->title)<span class="block truncate text-sm font-semibold">{{ $image->title }}</span>@endif
                    </span>
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white/15 backdrop-blur"><x-icon name="zoom-in" class="size-4" /></span>
                </span>
            </button>
        @endforeach
    </div>

    {{-- Lightbox --}}
    <template x-teleport="body">
        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[90] flex flex-col bg-black/95 text-white" role="dialog" aria-modal="true" aria-label="Image viewer">
            <div class="flex items-center justify-between gap-4 p-4">
                <p class="min-w-0 truncate text-sm"><span class="font-semibold text-accent" x-text="current?.title"></span></p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="toggleZoom()" class="grid size-10 place-items-center rounded-full bg-white/10 hover:bg-white/20" :aria-label="zoom > 1 ? 'Zoom out' : 'Zoom in'">
                        <x-icon name="zoom-in" class="size-5" x-show="zoom === 1" />
                        <x-icon name="zoom-out" class="size-5" x-show="zoom > 1" />
                    </button>
                    <button type="button" @click="close()" class="grid size-10 place-items-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Close"><x-icon name="x" class="size-5" /></button>
                </div>
            </div>
            <div class="relative flex flex-1 items-center justify-center overflow-auto px-4" @click.self="close()">
                <img :src="current?.src" :alt="current?.title" @click="toggleZoom()"
                     :class="zoom > 1 ? 'max-w-none cursor-zoom-out' : 'max-h-[78vh] max-w-full cursor-zoom-in'"
                     :style="zoom > 1 ? `width: ${zoom * 100}%` : ''"
                     class="rounded-brand object-contain transition-[width] duration-300 select-none">
                <button type="button" @click="step(-1)" class="absolute left-3 grid size-12 place-items-center rounded-full bg-white/10 hover:bg-white/20 sm:left-6" aria-label="Previous image"><x-icon name="chevron-left" class="size-6" /></button>
                <button type="button" @click="step(1)" class="absolute right-3 grid size-12 place-items-center rounded-full bg-white/10 hover:bg-white/20 sm:right-6" aria-label="Next image"><x-icon name="chevron-right" class="size-6" /></button>
            </div>
            <p class="px-4 py-4 text-center text-sm text-white/70" x-text="current?.caption"></p>
        </div>
    </template>
</div>
