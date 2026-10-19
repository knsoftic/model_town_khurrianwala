@extends('layouts.admin')

@section('title', 'Plot Sizes')

@section('content')
    <x-admin.page-header title="Plot Sizes" description="Every plot size shown on the website — prices, payment summary, availability and images.">
        <x-slot:actions>
            <a href="{{ route('admin.plots.availability') }}" class="a-btn-secondary"><x-icon name="grid-3x3" class="size-4" /> Availability</a>
            <a href="{{ route('admin.plots.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Plot Size</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1fr_180px_200px_170px_auto]">
        <x-admin.input name="q" :value="request('q')" placeholder="Search size or title…" />
        <x-admin.select name="status" :value="request('status')" :options="\App\Enums\PlotStatus::options()" placeholder="All statuses" />
        <x-admin.select name="location" :value="request('location')" :options="$locations" placeholder="All locations" />
        <x-admin.select name="visibility" :value="request('visibility')" :options="['active' => 'Visible on website', 'hidden' => 'Hidden', 'featured' => 'Featured']" placeholder="Any visibility" />
        <div class="flex gap-2">
            <button class="a-btn-primary"><x-icon name="search" class="size-4" /> Filter</button>
            @if ($filtered)
                <a href="{{ route('admin.plots.index') }}" class="a-btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    <x-admin.card :padding="false">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        @unless ($filtered)<th class="w-8"></th>@endunless
                        <th><x-admin.sort-link column="size_label" label="Plot size" /></th>
                        <th><x-admin.sort-link column="starting_price" label="Starting from" /></th>
                        <th>Booking / Installment</th>
                        <th>Units</th>
                        <th>Status</th>
                        <th class="text-center">Website</th>
                        <th class="text-center">Featured</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody @unless ($filtered) x-data="sortable(@js(route('admin.reorder', 'plots')))" @endunless>
                    @forelse ($plots as $plot)
                        <tr data-id="{{ $plot->id }}">
                            @unless ($filtered)
                                <td><span data-drag class="cursor-grab text-slate-400 hover:text-slate-700" title="Drag to reorder"><x-icon name="grip-vertical" class="size-4" /></span></td>
                            @endunless
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="size-14 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                        @if ($plot->image)<img src="{{ media_url($plot->image) }}" alt="" class="size-full object-cover">@endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.plots.edit', $plot) }}" class="font-semibold text-slate-900 hover:text-gold-700">{{ $plot->size_label }}</a>
                                        <p class="truncate text-xs text-slate-500">{{ $plot->title }} · {{ $plot->location?->name ?? 'No location' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $plot->installment_plans_count }} {{ str('plan')->plural($plot->installment_plans_count) }} · {{ $plot->leads_count }} leads · {{ $plot->booking_requests_count }} bookings</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="font-semibold text-slate-900">{{ money($plot->starting_price, false) }}</span>
                                <span class="block text-xs text-slate-500">Total {{ money($plot->total_price, false) }}</span>
                                @if ($plot->is_demo_price)<span class="a-badge mt-1 bg-amber-50 text-amber-700 ring-amber-600/20">Demo price</span>@endif
                            </td>
                            <td class="whitespace-nowrap text-slate-700">
                                {{ money($plot->booking_amount, false) }}
                                <span class="block text-xs text-slate-500">{{ money($plot->installment_amount, false) }} {{ $plot->frequencySuffix() }}</span>
                            </td>
                            <td class="min-w-32">
                                @php($pct = $plot->total_units ? round($plot->sold_units / $plot->total_units * 100) : 0)
                                <span class="text-xs font-semibold text-slate-700">{{ number_format($plot->sold_units) }} / {{ number_format($plot->total_units) }} sold</span>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gold-500" style="width: {{ $pct }}%"></div></div>
                                <span class="text-[11px] text-slate-500">{{ number_format($plot->availableUnits()) }} available</span>
                            </td>
                            <td><span class="a-badge {{ $plot->status->badgeClasses() }}">{{ $plot->status->label() }}</span></td>
                            <td class="text-center"><x-admin.switch :url="route('admin.toggle', ['plots', $plot->id])" :value="$plot->is_active" label="Show on website" /></td>
                            <td class="text-center"><x-admin.switch :url="route('admin.toggle', ['plots', $plot->id])" :value="$plot->is_featured" field="is_featured" label="Featured" /></td>
                            <td>
                                <x-admin.row-actions :edit="route('admin.plots.edit', $plot)" :delete="route('admin.plots.destroy', $plot)" :delete-label="$plot->size_label">
                                    <a href="{{ route('plots.show', $plot) }}" target="_blank" class="a-icon-btn" title="View on website"><x-icon name="external-link" class="size-4" /></a>
                                    <form method="POST" action="{{ route('admin.plots.duplicate', $plot) }}">@csrf<button class="a-icon-btn" title="Duplicate"><x-icon name="copy" class="size-4" /></button></form>
                                </x-admin.row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><x-admin.empty icon="land-plot" title="No plot sizes found" description="Add your first plot size to show it on the website."><a href="{{ route('admin.plots.create') }}" class="a-btn-gold">Add Plot Size</a></x-admin.empty></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    @unless ($filtered)
        <p class="mt-3 text-xs text-slate-500"><x-icon name="grip-vertical" class="inline size-3.5" /> Drag rows to change the order plot sizes appear on the website.</p>
    @endunless
    <div class="mt-4">{{ $plots->links() }}</div>
@endsection
