@extends('layouts.admin')

@section('title', $item->exists ? 'Edit Feature' : 'Add Feature')

@section('breadcrumbs')
    <a href="{{ route('admin.features.index') }}" class="hover:text-slate-900">Project Features</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $item->title ?? 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $item->exists ? route('admin.features.update', $item->id) : route('admin.features.store') }}" enctype="multipart/form-data">
        @csrf @if ($item->exists) @method('PUT') @endif
        <x-admin.page-header :title="$item->exists ? 'Edit '.$item->title : 'Add Feature'" />
        <x-admin.card class="max-w-3xl">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="title" label="Title" :value="$item->title" required class="sm:col-span-2" />
                <x-admin.textarea name="description" label="Description" :value="$item->description" rows="3" class="sm:col-span-2" />
                <x-admin.icon-picker :value="$item->icon" />
                <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$item->sort_order" />
                <x-admin.image name="image" label="Image (optional — replaces the icon)" :current="$item->image" class="sm:col-span-2" />
                <x-admin.toggle name="is_active" label="Show on website" :checked="$item->is_active" />
            </div>
        </x-admin.card>
        <x-admin.form-actions :cancel="route('admin.features.index')" />
    </form>
@endsection
