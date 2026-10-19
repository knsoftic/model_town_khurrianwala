@extends('layouts.admin')

@section('title', $video->exists ? 'Edit Video' : 'Add Video')

@section('breadcrumbs')
    <a href="{{ route('admin.videos.index') }}" class="hover:text-slate-900">Videos</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $video->title ?? 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $video->exists ? route('admin.videos.update', $video) : route('admin.videos.store') }}" enctype="multipart/form-data"
          x-data="{ provider: @js(old('provider', $video->provider?->value ?? 'youtube')) }">
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
                    <div class="sm:col-span-2" x-show="provider === 'self_hosted'" x-cloak>
                        <x-admin.field name="file" :field="['type' => 'video', 'label' => 'Video file']" :value="$video->file_path" />
                    </div>
                    <x-admin.textarea name="description" label="Description" :value="$video->description" rows="3" class="sm:col-span-2" />
                </div>
            </x-admin.card>
            <div class="space-y-6">
                <x-admin.card title="Thumbnail">
                    <x-admin.image name="thumbnail" :current="$video->thumbnail" help="Optional for YouTube (its own thumbnail is used)." />
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
