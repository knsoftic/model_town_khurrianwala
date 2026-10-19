@extends('layouts.admin')

@section('title', 'Contact Messages')

@section('content')
    <x-admin.page-header title="Contact Messages" :description="$unread.' unread · messages sent from the Contact page'" />

    <form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-[1fr_200px_auto]">
        <x-admin.input name="q" :value="request('q')" placeholder="Name, email, phone or subject…" />
        <x-admin.select name="filter" :value="request('filter')" :options="['unread' => 'Unread', 'read' => 'Read']" placeholder="All messages" />
        <div class="flex gap-2"><button class="a-btn-primary"><x-icon name="search" class="size-4" /> Filter</button>@if (request()->except('page'))<a href="{{ route('admin.messages.index') }}" class="a-btn-ghost">Reset</a>@endif</div>
    </form>

    <x-admin.card :padding="false">
        <ul class="divide-y divide-slate-100">
            @forelse ($messages as $item)
                <li class="flex items-start gap-4 px-5 py-4 transition hover:bg-slate-50">
                    <span @class(['mt-1.5 size-2.5 shrink-0 rounded-full', 'bg-gold-500' => ! $item->read_at, 'bg-transparent' => $item->read_at])></span>
                    <a href="{{ route('admin.messages.show', $item) }}" class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-x-3">
                            <span @class(['text-sm text-slate-900', 'font-bold' => ! $item->read_at, 'font-medium' => $item->read_at])>{{ $item->name }}</span>
                            <span class="text-xs text-slate-500">{{ $item->email }} {{ $item->phone }}</span>
                            @if ($item->lead_id)<span class="a-badge bg-emerald-50 text-emerald-700 ring-emerald-600/20">Converted to lead</span>@endif
                        </span>
                        <span class="mt-0.5 block truncate text-sm text-slate-600"><span class="font-medium">{{ $item->subject ?: 'No subject' }}</span> — {{ str($item->message)->limit(120) }}</span>
                    </a>
                    <span class="shrink-0 text-xs text-slate-400">{{ $item->created_at->diffForHumans() }}</span>
                    <x-admin.row-actions :delete="route('admin.messages.destroy', $item)" delete-label="this message" />
                </li>
            @empty
                <li><x-admin.empty icon="inbox" title="No messages" description="Messages from the Contact page appear here." /></li>
            @endforelse
        </ul>
    </x-admin.card>
    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
