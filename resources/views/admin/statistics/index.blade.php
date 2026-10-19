@extends('layouts.admin')

@section('title', 'Statistics')

@section('content')
    <x-admin.page-header title="Statistics" description="Counters such as “10+ Years Experience”. Homepage numbers appear in the introduction; About numbers on the About page.">
        <x-slot:actions><a href="{{ route('admin.statistics.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Statistic</a></x-slot:actions>
    </x-admin.page-header>

    @foreach (\App\Models\Statistic::GROUPS as $group => $label)
        @php($groupItems = $items->where('group', $group))
        <h2 class="mt-6 mb-3 text-sm font-semibold text-slate-700">{{ $label }}</h2>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" x-data="sortable(@js(route('admin.reorder', 'statistics')))">
            @forelse ($groupItems as $item)
                <div data-id="{{ $item->id }}" class="a-card p-4">
                    <div class="flex items-center justify-between">
                        <span data-drag class="cursor-grab text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-4" /></span>
                        <x-admin.switch :url="route('admin.toggle', ['statistics', $item->id])" :value="$item->is_active" />
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-xl bg-gold-100 text-gold-700"><x-icon :name="$item->icon ?: 'chart-no-axes-column'" class="size-5" /></span>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ $item->prefix }}{{ $item->value }}{{ $item->suffix }}</p>
                            <p class="text-xs text-slate-500">{{ $item->label }}</p>
                        </div>
                    </div>
                    <x-admin.row-actions class="mt-3" :edit="route('admin.statistics.edit', $item->id)" :delete="route('admin.statistics.destroy', $item->id)" :delete-label="$item->label" />
                </div>
            @empty
                <p class="text-sm text-slate-500">No statistics in this group.</p>
            @endforelse
        </div>
    @endforeach
@endsection
