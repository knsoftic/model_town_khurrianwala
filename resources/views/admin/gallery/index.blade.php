@extends('layouts.admin')

@section('title', 'Gallery')

@section('content')
    <x-admin.page-header title="Gallery" description="Albums become the category filters on the Gallery page. Drag albums to reorder.">
        <x-slot:actions><a href="{{ route('admin.videos.index') }}" class="a-btn-secondary"><x-icon name="clapperboard" class="size-4" /> Videos</a></x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-4">
        <div class="xl:col-span-3">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" x-data="sortable(@js(route('admin.reorder', 'albums')))">
                @forelse ($albums as $album)
                    <div data-id="{{ $album->id }}" class="a-card group overflow-hidden">
                        <a href="{{ route('admin.gallery.albums.show', $album) }}" class="relative block aspect-[4/3] bg-slate-100">
                            @if ($album->cover_image)<img src="{{ media_url($album->cover_image) }}" alt="" class="size-full object-cover transition group-hover:scale-[1.02]">@endif
                            <span class="absolute right-2 bottom-2 rounded-full bg-navy-900/85 px-2.5 py-1 text-xs font-semibold text-white">{{ $album->images_count }} {{ str('photo')->plural($album->images_count) }}</span>
                        </a>
                        <div class="flex items-center gap-3 p-3">
                            <span data-drag class="cursor-grab text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-4" /></span>
                            <a href="{{ route('admin.gallery.albums.show', $album) }}" class="min-w-0 flex-1 truncate font-semibold text-slate-900 hover:text-gold-700">{{ $album->name }}</a>
                            <x-admin.switch :url="route('admin.toggle', ['albums', $album->id])" :value="$album->is_active" label="Show on website" />
                        </div>
                    </div>
                @empty
                    <x-admin.card class="sm:col-span-2 lg:col-span-3"><x-admin.empty icon="images" title="No albums yet" description="Create an album, then upload photos to it." /></x-admin.card>
                @endforelse
            </div>
        </div>

        <form method="POST" action="{{ route('admin.gallery.albums.store') }}" class="xl:sticky xl:top-24 xl:self-start">
            @csrf
            <x-admin.card title="New album">
                <div class="space-y-4">
                    <x-admin.input name="name" label="Album name" required placeholder="e.g. Possession Ceremony" />
                    <x-admin.textarea name="description" label="Description" rows="2" />
                </div>
                <button class="a-btn-gold mt-4 w-full"><x-icon name="plus" class="size-4" /> Create album</button>
            </x-admin.card>
        </form>
    </div>
@endsection
