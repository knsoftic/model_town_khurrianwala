@extends('layouts.admin')

@section('title', $item->exists ? 'Edit Statistic' : 'Add Statistic')

@section('breadcrumbs')
    <a href="{{ route('admin.statistics.index') }}" class="hover:text-slate-900">Statistics</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $item->label ?? 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $item->exists ? route('admin.statistics.update', $item->id) : route('admin.statistics.store') }}">
        @csrf @if ($item->exists) @method('PUT') @endif
        <x-admin.page-header :title="$item->exists ? 'Edit '.$item->label : 'Add Statistic'" />
        <x-admin.card class="max-w-3xl">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.select name="group" label="Shown on" :value="$item->group" :options="\App\Models\Statistic::GROUPS" class="sm:col-span-3" />
                <x-admin.input name="prefix" label="Prefix" :value="$item->prefix" placeholder="e.g. PKR" />
                <x-admin.input name="value" label="Number / value" :value="$item->value" required placeholder="5000" help="Numbers animate when scrolled into view." />
                <x-admin.input name="suffix" label="Suffix" :value="$item->suffix" placeholder="+ or %" />
                <x-admin.input name="label" label="Label" :value="$item->label" required placeholder="Happy Clients" class="sm:col-span-2" />
                <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$item->sort_order" />
                <x-admin.icon-picker :value="$item->icon" />
                <div class="flex items-end pb-2 sm:col-span-2"><x-admin.toggle name="is_active" label="Show on website" :checked="$item->is_active" /></div>
            </div>
        </x-admin.card>
        <x-admin.form-actions :cancel="route('admin.statistics.index')" />
    </form>
@endsection
