@extends('layouts.admin')

@section('title', 'Development Progress')

@section('content')
    <x-admin.page-header title="Development Progress" :description="'Overall progress '.($items->isEmpty() ? 0 : round($items->avg('percentage'))).'% · milestones shown with animated bars on the website. Drag to reorder.'">
        <x-slot:actions><a href="{{ route('admin.development.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Milestone</a></x-slot:actions>
    </x-admin.page-header>

    <ol class="space-y-2" x-data="sortable(@js(route('admin.reorder', 'development')))">
        @forelse ($items as $item)
            <li data-id="{{ $item->id }}" class="a-card flex items-center gap-4 p-4">
                <span data-drag class="cursor-grab text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-5" /></span>
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gold-100 text-gold-700"><x-icon :name="$item->icon ?: 'hard-hat'" class="size-5" /></span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-slate-900">{{ $item->title }}</p>
                        <span class="a-badge {{ $item->status->badgeClasses() }}">{{ $item->status->label() }}</span>
                    </div>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gold-500" style="width: {{ $item->percentage }}%"></div></div>
                        <span class="w-10 text-right text-sm font-bold text-slate-900">{{ $item->percentage }}%</span>
                    </div>
                </div>
                <x-admin.switch :url="route('admin.toggle', ['development', $item->id])" :value="$item->is_active" />
                <x-admin.row-actions :edit="route('admin.development.edit', $item->id)" :delete="route('admin.development.destroy', $item->id)" :delete-label="$item->title" />
            </li>
        @empty
            <li class="a-card"><x-admin.empty icon="hard-hat" title="No milestones yet" /></li>
        @endforelse
    </ol>
@endsection
