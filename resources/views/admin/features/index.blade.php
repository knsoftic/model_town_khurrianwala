@extends('layouts.admin')

@section('title', 'Project Features')

@section('content')
    <x-admin.page-header title="Project Features" description="Amenities shown on the homepage, About page, plot pages and location pages. Drag to reorder.">
        <x-slot:actions><a href="{{ route('admin.features.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Feature</a></x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" x-data="sortable(@js(route('admin.reorder', 'features')))">
        @forelse ($items as $item)
            <div data-id="{{ $item->id }}" class="a-card flex items-start gap-3 p-4">
                <span data-drag class="mt-2 cursor-grab text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-4" /></span>
                @if ($item->image)
                    <img src="{{ media_url($item->image) }}" alt="" class="size-12 shrink-0 rounded-xl object-cover">
                @else
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-navy-900 text-gold-400"><x-icon :name="$item->icon ?: 'circle-check'" class="size-6" /></span>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-900">{{ $item->title }}</p>
                    <p class="line-clamp-2 text-xs text-slate-500">{{ $item->description }}</p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <x-admin.switch :url="route('admin.toggle', ['features', $item->id])" :value="$item->is_active" />
                    <x-admin.row-actions :edit="route('admin.features.edit', $item->id)" :delete="route('admin.features.destroy', $item->id)" :delete-label="$item->title" />
                </div>
            </div>
        @empty
            <x-admin.card class="sm:col-span-2 xl:col-span-3"><x-admin.empty icon="sparkles" title="No features yet" /></x-admin.card>
        @endforelse
    </div>
@endsection
