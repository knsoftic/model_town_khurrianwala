@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <x-admin.page-header title="Notifications" description="New leads, booking requests and contact messages.">
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">@csrf<button class="a-btn-secondary"><x-icon name="check-check" class="size-4" /> Mark all read</button></form>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.card :padding="false">
        <ul class="divide-y divide-slate-100">
            @forelse ($notifications as $notification)
                @php($kind = $notification->data['kind'] ?? 'lead')
                <li>
                    <a href="{{ route('admin.notifications.open', $notification->id) }}" @class(['flex items-start gap-4 px-5 py-4 transition hover:bg-slate-50', 'bg-gold-50/50' => ! $notification->read_at])>
                        <span @class(['grid size-10 shrink-0 place-items-center rounded-full', 'bg-sky-100 text-sky-700' => $kind === 'lead', 'bg-emerald-100 text-emerald-700' => $kind === 'booking', 'bg-violet-100 text-violet-700' => $kind === 'contact'])>
                            <x-icon :name="['lead' => 'user-plus', 'booking' => 'bookmark-check', 'contact' => 'mail'][$kind] ?? 'bell'" class="size-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Notification' }}</span>
                            <span class="block text-sm text-slate-600">{{ $notification->data['summary'] ?? '' }}</span>
                            <span class="mt-1 block text-xs text-slate-400">{{ $notification->created_at->format('d M Y, h:i A') }} · {{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        @unless ($notification->read_at)
                            <span class="mt-1 size-2.5 shrink-0 rounded-full bg-gold-500" title="Unread"></span>
                        @endunless
                    </a>
                </li>
            @empty
                <li><x-admin.empty icon="bell" title="No notifications yet" description="You'll be notified here when a lead, booking request or message arrives." /></li>
            @endforelse
        </ul>
    </x-admin.card>

    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
