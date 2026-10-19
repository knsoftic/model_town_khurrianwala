@extends('layouts.admin')

@section('title', 'Featured Plots')

@section('content')
    <x-admin.page-header title="Featured Plots" description="Star the plot sizes to highlight and drag the cards to set the order they appear on the website. Changes save instantly." />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" x-data="sortable(@js(route('admin.reorder', 'plots')))">
        @foreach ($plots as $plot)
            <div data-id="{{ $plot->id }}" class="a-card overflow-hidden">
                <div class="relative aspect-[4/3] bg-slate-100">
                    @if ($plot->image)<img src="{{ media_url($plot->image) }}" alt="" class="size-full object-cover">@endif
                    <span data-drag class="absolute top-2 left-2 grid size-8 cursor-grab place-items-center rounded-lg bg-white/95 text-slate-600 shadow" title="Drag to reorder"><x-icon name="grip-vertical" class="size-4" /></span>
                    <span class="a-badge absolute top-2 right-2 bg-white {{ $plot->status->badgeClasses() }}">{{ $plot->status->label() }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 p-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900">{{ $plot->size_label }}</p>
                        <p class="truncate text-xs text-slate-500">{{ money($plot->starting_price, false) }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2 text-xs text-slate-500">
                        <label class="flex items-center gap-2">Featured <x-admin.switch :url="route('admin.toggle', ['plots', $plot->id])" :value="$plot->is_featured" field="is_featured" label="Featured" /></label>
                        <label class="flex items-center gap-2">Visible <x-admin.switch :url="route('admin.toggle', ['plots', $plot->id])" :value="$plot->is_active" label="Show on website" /></label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
