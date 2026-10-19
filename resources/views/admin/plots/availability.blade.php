@extends('layouts.admin')

@section('title', 'Availability')

@section('content')
    <form method="POST" action="{{ route('admin.plots.availability.update') }}">
        @csrf @method('PUT')

        <x-admin.page-header title="Availability" description="Update stock and status badges for every plot size in one place.">
            <x-slot:actions><button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save all</button></x-slot:actions>
        </x-admin.page-header>

        <x-admin.card :padding="false">
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead><tr><th>Plot size</th><th>Status badge</th><th>Total plots</th><th>Sold</th><th>Available</th><th class="text-center">On website</th></tr></thead>
                    <tbody>
                        @foreach ($plots as $plot)
                            <tr x-data="{ total: {{ (int) old("plots.{$plot->id}.total_units", $plot->total_units) }}, sold: {{ (int) old("plots.{$plot->id}.sold_units", $plot->sold_units) }} }">
                                <td>
                                    <span class="font-semibold text-slate-900">{{ $plot->size_label }}</span>
                                    <span class="block text-xs text-slate-500">{{ $plot->title }}</span>
                                </td>
                                <td class="min-w-44"><x-admin.select :name="'plots['.$plot->id.'][status]'" :value="$plot->status" :options="\App\Enums\PlotStatus::options()" /></td>
                                <td class="w-36"><x-admin.input :name="'plots['.$plot->id.'][total_units]'" type="number" min="0" :value="$plot->total_units" x-model.number="total" /></td>
                                <td class="w-36"><x-admin.input :name="'plots['.$plot->id.'][sold_units]'" type="number" min="0" :value="$plot->sold_units" x-model.number="sold" /></td>
                                <td class="w-40">
                                    <span class="text-sm font-semibold" :class="total - sold <= 0 ? 'text-rose-600' : 'text-emerald-700'" x-text="Math.max(0, total - sold)"></span>
                                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gold-500 transition-all" :style="`width: ${total ? Math.min(100, sold / total * 100) : 0}%`"></div></div>
                                </td>
                                <td class="text-center">
                                    <input type="hidden" name="plots[{{ $plot->id }}][is_active]" value="0">
                                    <input type="checkbox" name="plots[{{ $plot->id }}][is_active]" value="1" @checked($plot->is_active) class="size-5 rounded border-slate-300 text-emerald-600 focus:ring-gold-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </form>
@endsection
