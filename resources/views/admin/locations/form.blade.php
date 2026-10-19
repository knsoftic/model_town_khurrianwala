@extends('layouts.admin')

@php($editing = $location->exists)

@section('title', $editing ? 'Edit '.$location->name : 'Add Location')

@section('breadcrumbs')
    <a href="{{ route('admin.locations.index') }}" class="hover:text-slate-900">Locations</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $location->name ?? 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.locations.update', $location) : route('admin.locations.store') }}" enctype="multipart/form-data">
        @csrf @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $location->name : 'Add Location'">
            <x-slot:actions>
                @if ($editing)<a href="{{ route('locations.show', $location) }}" target="_blank" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> View on website</a>@endif
                <button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save</button>
            </x-slot:actions>
        </x-admin.page-header>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <x-admin.card title="Location details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="name" label="Name" :value="$location->name" required class="sm:col-span-2" />
                        <x-admin.input name="slug" label="URL slug" :value="$location->slug" placeholder="Created from the name" />
                        <x-admin.input name="city" label="City" :value="$location->city" />
                        <x-admin.input name="area" label="Area" :value="$location->area" />
                        <x-admin.input name="address" label="Address" :value="$location->address" />
                        <x-admin.textarea name="short_description" label="Short description" :value="$location->short_description" rows="2" class="sm:col-span-2" />
                        <div class="sm:col-span-2"><x-admin.rich name="description" label="Full description" :value="$location->description" /></div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Map">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.textarea name="map_embed" label="Google Maps embed code or link" :value="$location->map_embed_url" rows="3" class="sm:col-span-2"
                                          help="Google Maps → Share → Embed a map → copy HTML, and paste it here. Leave empty to build the map from the coordinates or address." />
                        <x-admin.input name="latitude" type="number" step="0.0000001" label="Latitude" :value="$location->latitude" />
                        <x-admin.input name="longitude" type="number" step="0.0000001" label="Longitude" :value="$location->longitude" />
                        <x-admin.input name="google_maps_url" type="url" label="“Get directions” link" :value="$location->google_maps_url" class="sm:col-span-2" />
                    </div>
                    @if ($location->mapEmbedSrc())
                        <iframe src="{{ $location->mapEmbedSrc() }}" class="mt-4 h-64 w-full rounded-lg border border-slate-200" loading="lazy" title="Map preview"></iframe>
                    @endif
                </x-admin.card>

                <x-admin.card title="Distances & landmarks">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-admin.input name="distance_main_road" label="Main road" :value="$location->distance_main_road" placeholder="2 min" />
                        <x-admin.input name="distance_motorway" label="Motorway" :value="$location->distance_motorway" placeholder="20 min" />
                        <x-admin.input name="distance_airport" label="Airport" :value="$location->distance_airport" placeholder="30 min" />
                    </div>
                    <div class="mt-6" x-data="repeater(@js(array_values(old('landmarks', $location->landmarks ?? []))), { name: '', distance: '' })">
                        <span class="a-label">Nearby landmarks</span>
                        <div class="space-y-2">
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="flex gap-2">
                                    <input class="a-input flex-1" placeholder="Landmark (e.g. City Hospital)" x-model="row.name" :name="`landmarks[${index}][name]`">
                                    <input class="a-input w-40" placeholder="Distance" x-model="row.distance" :name="`landmarks[${index}][distance]`">
                                    <button type="button" class="a-icon-btn size-10 hover:!text-rose-600" @click="remove(index)" title="Remove"><x-icon name="trash" class="size-4" /></button>
                                </div>
                            </template>
                        </div>
                        <button type="button" class="a-btn-secondary a-btn-sm mt-3" @click="add()"><x-icon name="plus" class="size-3.5" /> Add landmark</button>
                    </div>
                </x-admin.card>

                <x-admin.seo-fields :seo="$location->seo" />
            </div>

            <div class="space-y-6">
                <x-admin.card title="Publishing">
                    <div class="space-y-4">
                        <x-admin.toggle name="is_active" label="Show on website" :checked="$location->is_active" />
                        <x-admin.toggle name="is_featured" label="Featured (homepage “Prime Location”)" :checked="$location->is_featured" />
                        <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$location->sort_order" />
                    </div>
                </x-admin.card>
                <x-admin.card title="Image">
                    <x-admin.image name="image" :current="$location->image" help="Shown when no map is set, and as the page banner." />
                </x-admin.card>
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.locations.index')" />
    </form>
@endsection
