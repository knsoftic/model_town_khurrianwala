@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
    <x-admin.page-header title="Activity Log" description="Who changed what, and when — sign-ins, failed sign-ins, and every create, update and delete in the admin panel." />

    <form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_150px_150px_auto]">
        <x-admin.input name="q" :value="request('q')" placeholder="Search description…" />
        <x-admin.select name="user" :value="request('user')" :options="$users" placeholder="All users" />
        <x-admin.select name="action" :value="request('action')" :options="$actions" placeholder="All actions" />
        <x-admin.input name="from" type="date" :value="request('from')" title="From" />
        <x-admin.input name="to" type="date" :value="request('to')" title="To" />
        <div class="flex gap-2"><button class="a-btn-primary"><x-icon name="search" class="size-4" /> Filter</button>@if (request()->except('page'))<a href="{{ route('admin.activity.index') }}" class="a-btn-ghost">Reset</a>@endif</div>
    </form>

    <x-admin.card :padding="false">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead><tr><th>When</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-xs text-slate-500" title="{{ $log->created_at?->format('d M Y, h:i:s A') }}">{{ $log->created_at?->diffForHumans() }}</td>
                            <td class="whitespace-nowrap text-sm">{{ $log->user?->name ?? 'System / visitor' }}</td>
                            <td><span class="a-badge ring-0 {{ $log->actionClasses() }}">{{ str($log->action)->replace('_', ' ')->title() }}</span></td>
                            <td class="text-sm text-slate-700">
                                {{ $log->description }}
                                @if (! empty($log->properties['changed']))
                                    <span class="block text-xs text-slate-400">Changed: {{ implode(', ', array_slice($log->properties['changed'], 0, 10)) }}</span>
                                @endif
                            </td>
                            <td class="text-xs text-slate-400">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-admin.empty icon="activity" title="No activity recorded" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
