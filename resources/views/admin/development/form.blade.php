@extends('layouts.admin')

@section('title', $item->exists ? 'Edit Milestone' : 'Add Milestone')

@section('breadcrumbs')
    <a href="{{ route('admin.development.index') }}" class="hover:text-slate-900">Development Progress</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $item->title ?? 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $item->exists ? route('admin.development.update', $item->id) : route('admin.development.store') }}" enctype="multipart/form-data">
        @csrf @if ($item->exists) @method('PUT') @endif
        <x-admin.page-header :title="$item->exists ? 'Edit '.$item->title : 'Add Milestone'" />
        <x-admin.card class="max-w-3xl">
            <div class="grid gap-4 sm:grid-cols-2" x-data="{ pct: {{ (int) old('percentage', $item->percentage) }} }">
                <x-admin.input name="title" label="Title" :value="$item->title" required class="sm:col-span-2" placeholder="e.g. Road Construction" />
                <div class="sm:col-span-2">
                    <label class="a-label" for="f-percentage">Progress: <span class="font-bold text-slate-900" x-text="pct + '%'"></span></label>
                    <input id="f-percentage" type="range" name="percentage" min="0" max="100" x-model.number="pct" class="w-full accent-[#c9a54c]">
                </div>
                <x-admin.select name="status" label="Status" :value="$item->status" :options="\App\Enums\MilestoneStatus::options()" />
                <x-admin.icon-picker :value="$item->icon" />
                <x-admin.textarea name="description" label="Description" :value="$item->description" rows="3" class="sm:col-span-2" />
                <x-admin.image name="image" label="Progress photo (shown on the Development page)" :current="$item->image" class="sm:col-span-2" />
                <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$item->sort_order" />
                <div class="flex items-end pb-2"><x-admin.toggle name="is_active" label="Show on website" :checked="$item->is_active" /></div>
            </div>
        </x-admin.card>
        <x-admin.form-actions :cancel="route('admin.development.index')" />
    </form>
@endsection
