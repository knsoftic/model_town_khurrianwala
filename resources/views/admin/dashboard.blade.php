@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $user = auth()->user();
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $cards = [
        ['Plot Sizes', number_format($stats['plot_sizes']), 'land-plot', 'navy', 'plots', 'admin.plots.index', 'Sizes listed on the website'],
        ['Available Plots', number_format($stats['available_plots']), 'grid-3x3', 'emerald', 'plots', 'admin.plots.availability', 'Units still open for booking'],
        ['Sold Plots', number_format($stats['sold_plots']), 'badge-check', 'gold', 'plots', 'admin.plots.availability', 'Units marked as sold'],
        ['Total Leads', number_format($stats['total_leads']), 'users-round', 'sky', 'leads', 'admin.leads.index', 'All inquiries received'],
        ['New Leads', number_format($stats['new_leads']), 'user-plus', 'violet', 'leads', 'admin.leads.index', 'Waiting for first contact'],
        ['Booking Requests', number_format($stats['booking_requests']), 'bookmark-check', 'amber', 'bookings', 'admin.bookings.index', $stats['pending_bookings'].' pending review'],
        ['Happy Clients', number_format($stats['happy_clients']), 'smile', 'emerald', 'leads', 'admin.leads.index', 'Leads booked or converted'],
        ['Testimonials', number_format($stats['testimonials']), 'quote', 'rose', 'testimonials', 'admin.testimonials.index', 'Published reviews'],
        ['Gallery Images', number_format($stats['gallery_images']), 'images', 'sky', 'gallery', 'admin.gallery.index', 'Across all albums'],
        ['Locations', number_format($stats['locations']), 'map-pin', 'slate', 'locations', 'admin.locations.index', 'Project locations'],
    ];
@endphp

@section('content')
    <x-admin.page-header :title="$greeting.', '.str($user->name)->before(' ')" :description="now()->format('l, j F Y').' · Here is what is happening on '.setting('site_name').'.'">
        <x-slot:actions>
            @can('plots')
                <a href="{{ route('admin.plots.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Plot Size</a>
            @endcan
            @can('leads')
                <a href="{{ route('admin.leads.create') }}" class="a-btn-secondary"><x-icon name="user-plus" class="size-4" /> Add Lead</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @if ($stats['follow_ups_due'] > 0 && $user->can('leads'))
        <a href="{{ route('admin.leads.index', ['follow_up' => 'due']) }}" class="mb-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 transition hover:bg-amber-100">
            <x-icon name="alarm-clock" class="size-5 text-amber-600" />
            <span><strong>{{ $stats['follow_ups_due'] }}</strong> {{ str('follow-up')->plural($stats['follow_ups_due']) }} due today or overdue.</span>
            <span class="ml-auto font-semibold">Open <x-icon name="arrow-right" class="inline size-4" /></span>
        </a>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @foreach ($cards as [$label, $value, $icon, $tone, $permission, $route, $hint])
            <x-admin.stat :label="$label" :value="$value" :icon="$icon" :tone="$tone" :hint="$hint" :href="$user->can($permission) ? route($route) : null" />
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-admin.card title="Leads over time" description="New inquiries per day, last 30 days" class="xl:col-span-2">
            <div x-data="chart(@js($charts['leads_over_time']))" class="h-72"><canvas x-ref="canvas"></canvas></div>
        </x-admin.card>
        <x-admin.card title="Inquiry sources" description="Where leads come from">
            <div x-data="chart(@js($charts['inquiry_sources']))" class="h-72"><canvas x-ref="canvas"></canvas></div>
        </x-admin.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <x-admin.card title="Monthly leads" description="Last 12 months">
            <div x-data="chart(@js($charts['monthly_leads']))" class="h-72"><canvas x-ref="canvas"></canvas></div>
        </x-admin.card>
        <x-admin.card title="Booking requests" description="By status, last 12 months">
            <div x-data="chart(@js($charts['booking_requests']))" class="h-72"><canvas x-ref="canvas"></canvas></div>
        </x-admin.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-admin.card title="Plot interest by size" description="Leads per plot size">
            <div x-data="chart(@js($charts['plot_interest']))" class="h-72"><canvas x-ref="canvas"></canvas></div>
        </x-admin.card>

        <x-admin.card title="Follow-ups" description="Due in the next 2 days or overdue" :padding="false" class="xl:col-span-2">
            @if ($followUps->isEmpty())
                <x-admin.empty icon="calendar-check" title="No follow-ups due" description="Set a follow-up date on any lead to see it here." />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($followUps as $lead)
                        <li>
                            <a href="{{ route('admin.leads.show', $lead) }}" class="flex items-center gap-4 px-5 py-3 transition hover:bg-slate-50">
                                <span @class(['grid size-10 shrink-0 place-items-center rounded-xl text-xs font-bold', 'bg-rose-100 text-rose-700' => $lead->follow_up_date->isPast() && ! $lead->follow_up_date->isToday(), 'bg-amber-100 text-amber-700' => $lead->follow_up_date->isToday(), 'bg-sky-100 text-sky-700' => $lead->follow_up_date->isFuture()])>
                                    {{ $lead->follow_up_date->format('d') }}<br>{{ $lead->follow_up_date->format('M') }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $lead->name }}</span>
                                    <span class="block truncate text-xs text-slate-500">{{ $lead->phone }} · {{ $lead->plot?->size_label ?? 'No plot selected' }}</span>
                                </span>
                                <span class="a-badge {{ $lead->status->badgeClasses() }}">{{ $lead->status->label() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        @can('leads')
            <x-admin.card title="Recent leads" :padding="false" class="xl:col-span-2">
                <x-slot:actions><a href="{{ route('admin.leads.index') }}" class="a-btn-ghost a-btn-sm">View all</a></x-slot:actions>
                <div class="overflow-x-auto">
                    <table class="a-table">
                        <thead><tr><th>Name</th><th>Plot</th><th>Source</th><th>Status</th><th>Received</th></tr></thead>
                        <tbody>
                            @forelse ($recentLeads as $lead)
                                <tr class="cursor-pointer" onclick="window.location='{{ route('admin.leads.show', $lead) }}'">
                                    <td><span class="font-semibold text-slate-900">{{ $lead->name }}</span><span class="block text-xs text-slate-500">{{ $lead->phone }}</span></td>
                                    <td>{{ $lead->plot?->size_label ?? '—' }}</td>
                                    <td class="text-slate-600">{{ $lead->source->label() }}</td>
                                    <td><span class="a-badge {{ $lead->status->badgeClasses() }}">{{ $lead->status->label() }}</span></td>
                                    <td class="whitespace-nowrap text-slate-500">{{ $lead->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><x-admin.empty title="No leads yet" description="Inquiries from the website will appear here." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        @endcan

        @can('bookings')
            <x-admin.card title="Pending booking requests" :padding="false">
                <x-slot:actions><a href="{{ route('admin.bookings.index') }}" class="a-btn-ghost a-btn-sm">View all</a></x-slot:actions>
                @if ($pendingBookings->isEmpty())
                    <x-admin.empty icon="bookmark-check" title="Nothing pending" description="New booking requests will appear here." />
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($pendingBookings as $booking)
                            <li>
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="block px-5 py-3 transition hover:bg-slate-50">
                                    <span class="flex items-center justify-between gap-3">
                                        <span class="truncate text-sm font-semibold text-slate-900">{{ $booking->name }}</span>
                                        <span class="font-mono text-[11px] text-slate-500">{{ $booking->reference }}</span>
                                    </span>
                                    <span class="mt-0.5 block text-xs text-slate-500">{{ $booking->plot?->size_label ?? 'Plot removed' }} · {{ $booking->created_at->diffForHumans() }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        @endcan
    </div>
@endsection
