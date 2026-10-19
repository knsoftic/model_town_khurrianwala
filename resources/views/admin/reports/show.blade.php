@extends('layouts.admin')

@section('title', $report['title'])

@section('breadcrumbs')
    <a href="{{ route('admin.reports.index') }}" class="hover:text-slate-900">Reports</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $report['title'] }}</span>
@endsection

@php
    $shown = array_slice($report['rows'], 0, 500);
    $query = request()->query();
@endphp

@section('content')
    <x-admin.page-header :title="$report['title']" :description="$report['description']">
        <x-slot:actions>
            <a href="{{ route('admin.reports.export', [$key, 'csv'] + $query) }}" class="a-btn-secondary"><x-icon name="file-text" class="size-4" /> CSV</a>
            <a href="{{ route('admin.reports.export', [$key, 'xlsx'] + $query) }}" class="a-btn-secondary"><x-icon name="file-spreadsheet" class="size-4" /> Excel</a>
            <a href="{{ route('admin.reports.export', [$key, 'pdf'] + $query) }}" class="a-btn-primary"><x-icon name="file-down" class="size-4" /> PDF</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($filters)
        <form method="GET" class="a-card mb-4 flex flex-wrap items-end gap-3 p-4">
            @if (in_array('date', $filters, true))
                <x-admin.input name="from" type="date" label="From" :value="request('from')" />
                <x-admin.input name="to" type="date" label="To" :value="request('to')" />
            @endif
            @if (in_array('status', $filters, true))
                <x-admin.select name="status" label="Status" :value="request('status')" :options="\App\Enums\LeadStatus::options()" placeholder="Any" />
            @endif
            @if (in_array('booking_status', $filters, true))
                <x-admin.select name="booking_status" label="Status" :value="request('booking_status')" :options="\App\Enums\BookingStatus::options()" placeholder="Any" />
            @endif
            @if (in_array('source', $filters, true))
                <x-admin.select name="source" label="Source" :value="request('source')" :options="\App\Enums\LeadSource::options()" placeholder="Any" />
            @endif
            @if (in_array('plot', $filters, true))
                <x-admin.select name="plot" label="Plot size" :value="request('plot')" :options="$plots" placeholder="Any" />
            @endif
            <button class="a-btn-primary"><x-icon name="search" class="size-4" /> Apply</button>
            @if ($query)<a href="{{ route('admin.reports.show', $key) }}" class="a-btn-ghost">Reset</a>@endif
        </form>
    @endif

    @if ($report['summary'])
        <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($report['summary'] as $label => $value)
                <div class="a-card p-4"><p class="text-xs text-slate-500">{{ $label }}</p><p class="mt-1 text-xl font-bold text-slate-900">{{ $value }}</p></div>
            @endforeach
        </div>
    @endif

    <x-admin.card :padding="false">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead><tr>@foreach ($report['headings'] as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
                <tbody>
                    @forelse ($shown as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td class="whitespace-nowrap">{{ is_float($cell) ? number_format($cell) : $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($report['headings']) }}"><x-admin.empty icon="chart-pie" title="No data for these filters" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    @if (count($report['rows']) > count($shown))
        <p class="mt-3 text-xs text-slate-500">Showing the first {{ count($shown) }} of {{ number_format(count($report['rows'])) }} rows. Export to get them all.</p>
    @endif
@endsection
