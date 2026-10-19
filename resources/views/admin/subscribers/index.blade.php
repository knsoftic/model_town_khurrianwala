@extends('layouts.admin')

@section('title', 'Subscribers')

@section('content')
    <x-admin.page-header title="Newsletter Subscribers" :description="$active.' active subscribers from the footer newsletter box'">
        <x-slot:actions><a href="{{ route('admin.subscribers.export') }}" class="a-btn-secondary"><x-icon name="download" class="size-4" /> Export CSV</a></x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="a-card mb-4 flex gap-3 p-4">
        <x-admin.input name="q" :value="request('q')" placeholder="Search email…" class="flex-1" />
        <button class="a-btn-primary"><x-icon name="search" class="size-4" /> Search</button>
    </form>

    <x-admin.card :padding="false">
        <table class="a-table">
            <thead><tr><th>Email</th><th>Subscribed</th><th class="text-center">Active</th><th></th></tr></thead>
            <tbody>
                @forelse ($subscribers as $subscriber)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $subscriber->email }}</td>
                        <td class="text-slate-500">{{ $subscriber->created_at->format('d M Y') }}</td>
                        <td class="text-center"><x-admin.switch :url="route('admin.toggle', ['subscribers', $subscriber->id])" :value="$subscriber->is_active" /></td>
                        <td><x-admin.row-actions :delete="route('admin.subscribers.destroy', $subscriber)" :delete-label="$subscriber->email" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-admin.empty icon="mail-plus" title="No subscribers yet" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.card>
    <div class="mt-4">{{ $subscribers->links() }}</div>
@endsection
