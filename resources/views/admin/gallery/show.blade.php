@extends('layouts.admin')

@section('title', $album->name)

@section('breadcrumbs')
    <a href="{{ route('admin.gallery.index') }}" class="hover:text-slate-900">Gallery</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $album->name }}</span>
@endsection

@section('content')
    <x-admin.page-header :title="$album->name" :description="$album->images->count().' photos · drag photos to reorder, switch them on/off, or edit their captions.'">
        <x-slot:actions>
            <a href="{{ route('gallery') }}" target="_blank" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> View gallery</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-4">
        <div class="space-y-6 xl:col-span-3">
            <form method="POST" action="{{ route('admin.gallery.images.store', $album) }}" enctype="multipart/form-data" x-data="{ files: [] }">
                @csrf
                <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 bg-white px-4 py-8 text-center text-sm text-slate-500 transition hover:border-gold-500 hover:bg-gold-50/40"
                       @dragover.prevent @drop.prevent="$refs.input.files = $event.dataTransfer.files; files = [...$event.dataTransfer.files].map(f => ({ name: f.name, url: URL.createObjectURL(f) }))">
                    <x-icon name="cloud-upload" class="size-9 text-slate-400" />
                    <span><span class="font-semibold text-gold-700">Choose photos</span> or drag them here — up to 30 at a time (JPG, PNG, WebP)</span>
                    <input x-ref="input" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only"
                           @change="files = [...$event.target.files].map(f => ({ name: f.name, url: URL.createObjectURL(f) }))">
                </label>
                <div x-show="files.length" x-cloak class="mt-3 rounded-xl border border-slate-200 bg-white p-3">
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-8">
                        <template x-for="file in files" :key="file.url"><img :src="file.url" :alt="file.name" class="aspect-square w-full rounded-md object-cover"></template>
                    </div>
                    <button class="a-btn-gold mt-3"><x-icon name="upload" class="size-4" /> Upload <span x-text="files.length"></span> photos</button>
                </div>
                @error('images')<p class="a-error">{{ $message }}</p>@enderror
                @error('images.*')<p class="a-error">{{ $message }}</p>@enderror
            </form>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" x-data="sortable(@js(route('admin.reorder', 'gallery-images')))">
                @forelse ($album->images as $image)
                    <div data-id="{{ $image->id }}" class="a-card overflow-hidden" x-data="{ edit: false }">
                        <div class="relative aspect-[4/3] bg-slate-100">
                            <img src="{{ media_url($image->path) }}" alt="" class="size-full object-cover" loading="lazy">
                            <span data-drag class="absolute top-2 left-2 grid size-8 cursor-grab place-items-center rounded-lg bg-white/95 text-slate-600 shadow"><x-icon name="grip-vertical" class="size-4" /></span>
                            @if ($album->cover_image === $image->path)<span class="a-badge absolute top-2 right-2 bg-gold-500 text-navy-900 ring-0">Cover</span>@endif
                        </div>
                        <div class="flex items-center gap-2 p-3">
                            <p class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800">{{ $image->title ?: 'Untitled' }}</p>
                            <x-admin.switch :url="route('admin.toggle', ['gallery-images', $image->id])" :value="$image->is_active" label="Show on website" />
                            <button type="button" class="a-icon-btn" @click="edit = !edit" title="Edit"><x-icon name="pencil" class="size-4" /></button>
                            <button type="button" class="a-icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="Delete" @click="$store.confirm.ask({ action: @js(route('admin.gallery.images.destroy', $image)), title: 'Delete this photo?' })"><x-icon name="trash" class="size-4" /></button>
                        </div>
                        <form x-show="edit" x-collapse method="POST" action="{{ route('admin.gallery.images.update', $image) }}" class="space-y-3 border-t border-slate-100 bg-slate-50/60 p-3">
                            @csrf @method('PUT')
                            <input name="title" value="{{ $image->title }}" placeholder="Title" class="a-input">
                            <textarea name="caption" rows="2" placeholder="Caption" class="a-input">{{ $image->caption }}</textarea>
                            <select name="gallery_album_id" class="a-input" title="Album">
                                @foreach ($albums as $id => $name)<option value="{{ $id }}" @selected($image->gallery_album_id === $id)>{{ $name }}</option>@endforeach
                            </select>
                            <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="make_cover" value="1" class="rounded border-slate-300 text-navy-900"> Use as album cover</label>
                            <button class="a-btn-primary a-btn-sm"><x-icon name="check" class="size-3.5" /> Save</button>
                        </form>
                    </div>
                @empty
                    <x-admin.card class="sm:col-span-2 lg:col-span-3"><x-admin.empty icon="image" title="No photos in this album yet" /></x-admin.card>
                @endforelse
            </div>
        </div>

        <form method="POST" action="{{ route('admin.gallery.albums.update', $album) }}" enctype="multipart/form-data" class="space-y-6 xl:sticky xl:top-24 xl:self-start">
            @csrf @method('PUT')
            <x-admin.card title="Album settings">
                <div class="space-y-4">
                    <x-admin.input name="name" label="Name" :value="$album->name" required />
                    <x-admin.input name="slug" label="Filter key" :value="$album->slug" required />
                    <x-admin.textarea name="description" label="Description" :value="$album->description" rows="2" />
                    <x-admin.image name="cover_image" label="Cover image" :current="$album->cover_image" aspect="aspect-[4/3]" />
                    <x-admin.toggle name="is_active" label="Show on website" :checked="$album->is_active" />
                </div>
                <button class="a-btn-primary mt-5 w-full"><x-icon name="check" class="size-4" /> Save album</button>
                <button type="button" class="a-btn-ghost a-btn-sm mt-2 w-full text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.gallery.albums.destroy', $album)), title: 'Delete this album?', message: 'Its photos will be hidden from the website.' })"><x-icon name="trash" class="size-3.5" /> Delete album</button>
            </x-admin.card>
        </form>
    </div>
@endsection
