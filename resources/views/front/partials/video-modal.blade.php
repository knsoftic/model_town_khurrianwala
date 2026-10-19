@once
    <div x-data="videoModal" x-cloak x-show="open" @play-video.window="play($event.detail.src, $event.detail.type ?? 'embed')" @keydown.escape.window="close()"
         class="fixed inset-0 z-[90] grid place-items-center bg-black/85 p-4" role="dialog" aria-modal="true" aria-label="Video player">
        <button type="button" @click="close()" class="absolute top-4 right-4 grid size-11 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Close video">
            <x-icon name="x" class="size-5" />
        </button>
        <div class="w-full max-w-5xl overflow-hidden rounded-brand-lg bg-black shadow-2xl" @click.outside="close()">
            <template x-if="open && type === 'embed'">
                <iframe :src="src" class="aspect-video w-full" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen title="Video"></iframe>
            </template>
            <template x-if="open && type === 'file'">
                <video :src="src" class="aspect-video w-full" controls autoplay playsinline></video>
            </template>
        </div>
    </div>
@endonce
