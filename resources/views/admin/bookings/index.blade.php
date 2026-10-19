@extends('layouts.admin')

@section('title', 'Booking Requests')

@section('content')
    <x-admin.page-header title="Booking Requests" description="Requests submitted from “Book Your Plot”. Approve, reject, hold or contact the customer.">
        <x-slot:actions>
            <a href="{{ route('admin.bookings.export', ['csv'] + request()->query()) }}" class="a-btn-secondary"><x-icon name="file-text" class="size-4" /> CSV</a>
            <a href="{{ route('admin.bookings.export', ['xlsx'] + request()->query()) }}" class="a-btn-secondary"><x-icon name="file-spreadsheet" class="size-4" /> Excel</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="scrollbar-none mb-4 flex gap-2 overflow-x-auto pb-1">
        @php($current = request('status'))
        <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" @class(['a-btn a-btn-sm shrink-0 border', 'border-navy-900 bg-navy-900 text-white' => ! $current, 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $current])>All <span class="opacity-70">{{ $counts->sum() }}</span></a>
        @foreach (\App\Enums\BookingStatus::cases() as $status)
            <a href="{{ request()->fullUrlWithQuery(['status' => $status->value, 'page' => null]) }}" @class(['a-btn a-btn-sm shrink-0 border', 'border-navy-900 bg-navy-900 text-white' => $current === $status->value, 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $current !== $status->value])>
                {{ $status->label() }} <span class="opacity-70">{{ $counts[$status->value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_160px_160px_auto]">
        @if ($current)<input type="hidden" name="status" value="{{ $current }}">@endif
        <x-admin.input name="q" :value="request('q')" placeholder="Reference, name, phone or email…" />
        <x-admin.select name="plot" :value="request('plot')" :options="$plots" placeholder="Any plot size" />
        <x-admin.input name="from" type="date" :value="request('from')" title="From date" />
        <x-admin.input name="to" type="date" :value="request('to')" title="To date" />
        <div class="flex gap-2"><button class="a-btn-primary"><x-icon name="search" class="size-4" /> Filter</button>@if (request()->except('page'))<a href="{{ route('admin.bookings.index') }}" class="a-btn-ghost">Reset</a>@endif</div>
    </form>

    <x-admin.card :padding="false">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead><tr><th>Reference</th><th>Customer</th><th>Plot / plan</th><th>City</th><th>Status</th><th>Received</th><th></th></tr></thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td><a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono text-sm font-semibold text-slate-900 hover:text-gold-700">{{ $booking->reference }}</a></td>
                            <td><span class="font-semibold text-slate-900">{{ $booking->name }}</span><span class="block text-xs text-slate-500">{{ $booking->phone }}</span></td>
                            <td><span class="font-medium">{{ $booking->plot?->size_label ?? '—' }}</span><span class="block max-w-52 truncate text-xs text-slate-500">{{ $booking->installmentPlan?->name ?? 'No plan selected' }}</span></td>
                            <td class="text-slate-600">{{ $booking->city ?? '—' }}</td>
                            <td x-data="inlineSelect(@js(route('admin.bookings.update', $booking)), 'status')">
                                <select @change="save($event)" class="a-input w-40 py-1 text-xs" aria-label="Status">
                                    @foreach (\App\Enums\BookingStatus::options() as $value => $label)
                                        <option value="{{ $value }}" @selected($booking->status->value === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="whitespace-nowrap text-xs text-slate-500" title="{{ $booking->created_at->format('d M Y, h:i A') }}">{{ $booking->created_at->diffForHumans() }}</td>
                            <td><div class="flex justify-end"><a href="{{ route('admin.bookings.show', $booking) }}" class="a-btn-secondary a-btn-sm">Review</a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-admin.empty icon="bookmark-check" title="No booking requests" description="Requests from the website's “Book Your Plot” page appear here." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <div class="mt-4">{{ $bookings->links() }}</div>
@endsection
