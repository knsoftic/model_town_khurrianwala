@extends('layouts.admin')

@section('title', 'Locations')

@section('content')
    <x-admin.page-header title="Locations" description="Project locations with maps, landmarks and distances. The featured location appears in the homepage “Prime Location” section.">
        <x-slot:actions><a href="{{ route('admin.locations.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Location</a></x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" x-data="sortable(@js(route('admin.reorder', 'locations')))">
        @forelse ($locations as $location)
            <div data-id="{{ $location->id }}" class="a-card overflow-hidden">
                <div class="relative aspect-[16/9] bg-slate-100">
                    @if ($location->image)<img src="{{ media_url($location->image) }}" alt="" class="size-full object-cover">@endif
                    <span data-drag class="absolute top-2 left-2 grid size-8 cursor-grab place-items-center rounded-lg bg-white/95 text-slate-600 shadow"><x-icon name="grip-vertical" class="size-4" /></span>
                    @if ($location->is_featured)<span class="a-badge absolute top-2 right-2 bg-gold-500 text-navy-900 ring-0">Featured</span>@endif
                </div>
                <div class="p-4">
                    <a href="{{ route('admin.locations.edit', $location) }}" class="font-semibold text-slate-900 hover:text-gold-700">{{ $location->name }}</a>
                    <p class="text-xs text-slate-500">{{ $location->fullAddress() ?: 'No address' }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $location->plots_count }} plot {{ str('size')->plural($location->plots_count) }} · {{ count($location->landmarks ?? []) }} landmarks</p>
                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                        <div class="flex items-center gap-4 text-xs text-slate-500">
                            <label class="flex items-center gap-2">Visible <x-admin.switch :url="route('admin.toggle', ['locations', $location->id])" :value="$location->is_active" /></label>
                            <label class="flex items-center gap-2">Featured <x-admin.switch :url="route('admin.toggle', ['locations', $location->id])" :value="$location->is_featured" field="is_featured" /></label>
                        </div>
                        <x-admin.row-actions :edit="route('admin.locations.edit', $location)" :delete="route('admin.locations.destroy', $location)" :delete-label="$location->name" />
                    </div>
                </div>
            </div>
        @empty
            <x-admin.card class="md:col-span-2 xl:col-span-3"><x-admin.empty icon="map-pin" title="No locations yet" /></x-admin.card>
        @endforelse
    </div>
@endsection
