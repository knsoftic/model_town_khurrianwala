@extends('layouts.admin')

@section('title', 'Videos')

@section('content')
    <x-admin.page-header title="Videos" description="YouTube, Vimeo or uploaded videos. The featured video plays in the homepage “Discover Our Project” section and on the About page.">
        <x-slot:actions><a href="{{ route('admin.videos.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Video</a></x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" x-data="sortable(@js(route('admin.reorder', 'videos')))">
        @forelse ($videos as $video)
            <div data-id="{{ $video->id }}" class="a-card overflow-hidden">
                <div class="relative aspect-video bg-navy-900">
                    @if ($video->thumbnailUrl())<img src="{{ $video->thumbnailUrl() }}" alt="" class="size-full object-cover opacity-80">@endif
                    <span class="absolute inset-0 grid place-items-center"><span class="grid size-12 place-items-center rounded-full bg-gold-500 text-navy-900"><x-icon name="play" class="size-5" /></span></span>
                    <span data-drag class="absolute top-2 left-2 grid size-8 cursor-grab place-items-center rounded-lg bg-white/95 text-slate-600 shadow"><x-icon name="grip-vertical" class="size-4" /></span>
                    @if ($video->is_featured)<span class="a-badge absolute top-2 right-2 bg-gold-500 text-navy-900 ring-0">Featured</span>@endif
                </div>
                <div class="p-4">
                    <p class="font-semibold text-slate-900">{{ $video->title }}</p>
                    <p class="text-xs text-slate-500">{{ $video->provider->label() }} @if (! $video->embedUrl() && ! $video->fileUrl())· <span class="text-amber-600">no video link yet</span>@endif</p>
                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">Visible <x-admin.switch :url="route('admin.toggle', ['videos', $video->id])" :value="$video->is_active" /></label>
                            <label class="flex items-center gap-2">Featured <x-admin.switch :url="route('admin.toggle', ['videos', $video->id])" :value="$video->is_featured" field="is_featured" /></label>
                        </div>
                        <x-admin.row-actions :edit="route('admin.videos.edit', $video)" :delete="route('admin.videos.destroy', $video)" :delete-label="$video->title" />
                    </div>
                </div>
            </div>
        @empty
            <x-admin.card class="sm:col-span-2 xl:col-span-3"><x-admin.empty icon="clapperboard" title="No videos yet" /></x-admin.card>
        @endforelse
    </div>
@endsection
