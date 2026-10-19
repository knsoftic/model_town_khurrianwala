@props(['video'])

@php
    $embed = $video->embedUrl();
    $file = $video->fileUrl();
    $playable = $embed || $file;
    $thumb = $video->thumbnailUrl();
@endphp

<div {{ $attributes->class(['group relative overflow-hidden rounded-brand-lg bg-primary shadow-lift']) }}>
    @if ($thumb)
        <img src="{{ $thumb }}" alt="{{ $video->title }}" loading="lazy" class="aspect-video w-full object-cover opacity-80 transition duration-700 group-hover:scale-[1.02] group-hover:opacity-70">
    @else
        <div class="aspect-video w-full bg-grid-light"></div>
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/20 to-transparent"></div>

    <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center text-white">
        @if ($playable)
            <button type="button" @click="$dispatch('play-video', { src: @js($embed ?? $file), type: @js($embed ? 'embed' : 'file') })"
                    class="relative grid size-20 place-items-center rounded-full bg-accent text-primary shadow-2xl transition hover:scale-105 sm:size-24" aria-label="Play {{ $video->title }}">
                <span class="absolute inset-0 animate-ping rounded-full bg-accent/50"></span>
                <x-icon name="play" class="relative ml-1 size-8 fill-current sm:size-10" />
            </button>
        @else
            <span class="grid size-20 place-items-center rounded-full border border-white/30 bg-white/10 backdrop-blur sm:size-24"><x-icon name="clapperboard" class="size-8" /></span>
            <span class="mt-4 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide uppercase backdrop-blur">Video coming soon</span>
        @endif
    </div>

    <div class="absolute inset-x-0 bottom-0 p-6 text-white sm:p-8">
        <h3 class="font-heading text-xl font-semibold sm:text-2xl">{{ $video->title }}</h3>
        @if ($video->description)
            <p class="mt-1 max-w-2xl text-sm text-white/70">{{ $video->description }}</p>
        @endif
    </div>
</div>
