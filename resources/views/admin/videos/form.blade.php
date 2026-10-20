@extends('layouts.admin')

@section('title', $video->exists ? 'Edit Video' : 'Add Video')

@section('breadcrumbs')
    <a href="{{ route('admin.videos.index') }}" class="hover:text-slate-900">Videos</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $video->title ?? 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $video->exists ? route('admin.videos.update', $video) : route('admin.videos.store') }}" enctype="multipart/form-data"
          x-data="videoForm({ provider: @js(old('provider', $video->provider?->value ?? 'youtube')), hasThumbnail: @js((bool) $video->thumbnail), maxBytes: {{ (int) config('cms.uploads.video_max_kb') * 1024 }} })">
        @csrf @if ($video->exists) @method('PUT') @endif
        <x-admin.page-header :title="$video->exists ? $video->title : 'Add Video'" />

        <div class="grid gap-6 xl:grid-cols-3">
            <x-admin.card title="Video" class="xl:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="title" label="Title" :value="$video->title" required class="sm:col-span-2" />
                    <div class="sm:col-span-2">
                        <span class="a-label">Source</span>
                        <div class="flex gap-2">
                            @foreach (\App\Enums\VideoProvider::cases() as $case)
                                <label class="flex-1 cursor-pointer rounded-lg border px-3 py-2.5 text-center text-sm font-semibold transition" :class="provider === @js($case->value) ? 'border-gold-500 bg-gold-50 text-navy-900' : 'border-slate-200 text-slate-600'">
                                    <input type="radio" name="provider" value="{{ $case->value }}" x-model="provider" class="sr-only"> {{ $case->label() }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="sm:col-span-2" x-show="provider !== 'self_hosted'">
                        <x-admin.input name="url" type="url" label="Video link" :value="$video->url" placeholder="https://www.youtube.com/watch?v=…" />
                    </div>
                    <div class="sm:col-span-2" x-show="provider === 'self_hosted'" x-cloak x-data="{ removed: false }">
                        <span class="a-label">Video file</span>
                        @if ($video->file_path)
                            <div class="mb-2 flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2 text-sm" x-show="!removed">
                                <x-icon name="clapperboard" class="size-4 text-slate-500" />
                                <a href="{{ media_url($video->file_path) }}" target="_blank" class="truncate text-sky-700 underline">{{ basename($video->file_path) }}</a>
                                <button type="button" class="ml-auto text-xs font-semibold text-rose-600" @click="removed = true">Remove</button>
                            </div>
                        @endif
                        <input type="hidden" name="remove_file" :value="removed ? 1 : 0">
                        <input type="file" name="file" accept="video/mp4,video/webm" @change="videoChosen()"
                               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold hover:file:bg-slate-200">
                        <p class="a-help">MP4 or WebM, max {{ round(config('cms.uploads.video_max_kb') / 1024) }} MB. A thumbnail is made from the video automatically.</p>
                        @error('file')<p class="a-error">{{ $message }}</p>@enderror
                    </div>
                    <p x-show="status" x-text="status" x-cloak class="rounded-lg bg-sky-50 px-3 py-2 text-sm text-sky-800 sm:col-span-2"></p>
                    <x-admin.textarea name="description" label="Description" :value="$video->description" rows="3" class="sm:col-span-2" />
                </div>
            </x-admin.card>
            <div class="space-y-6">
                <x-admin.card title="Thumbnail">
                    <x-admin.image name="thumbnail" :current="$video->thumbnail" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm"
                                   help="Optional. YouTube and Vimeo use their own thumbnail; for an uploaded video one is made from the video — or choose your own image." />
                    <button type="button" x-show="provider === 'self_hosted'" x-cloak @click="makeThumbnail()" class="a-btn-ghost a-btn-sm mt-3">
                        <x-icon name="wand-sparkles" class="size-3.5" /> Make from video
                    </button>
                </x-admin.card>
                <x-admin.card title="Publishing">
                    <div class="space-y-4">
                        <x-admin.toggle name="is_active" label="Show on website" :checked="$video->is_active" />
                        <x-admin.toggle name="is_featured" label="Featured (homepage video)" :checked="$video->is_featured" />
                        <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$video->sort_order" />
                    </div>
                </x-admin.card>
            </div>
        </div>
        <x-admin.form-actions :cancel="route('admin.videos.index')" />
    </form>
@endsection
