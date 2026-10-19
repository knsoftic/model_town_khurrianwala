@extends('layouts.admin')

@section('title', 'Message from '.$item->name)

@section('breadcrumbs')
    <a href="{{ route('admin.messages.index') }}" class="hover:text-slate-900">Contact Messages</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $item->name }}</span>
@endsection

@section('content')
    <x-admin.page-header :title="$item->subject ?: 'Message from '.$item->name" :description="'Received '.$item->created_at->format('d M Y, h:i A')">
        <x-slot:actions>
            @if ($item->email)<a href="mailto:{{ $item->email }}?subject={{ rawurlencode('Re: '.($item->subject ?: 'Your message')) }}" class="a-btn-secondary"><x-icon name="reply" class="size-4" /> Reply by email</a>@endif
            @if ($item->phone)<a href="https://wa.me/{{ preg_replace('/\D/', '', $item->phone) }}" target="_blank" rel="noopener" class="a-btn bg-emerald-600 text-white hover:bg-emerald-500"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>@endif
            <form method="POST" action="{{ route('admin.messages.convert', $item) }}">@csrf<button class="a-btn-gold"><x-icon name="user-plus" class="size-4" /> {{ $item->lead_id ? 'Open lead' : 'Convert to lead' }}</button></form>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-admin.card title="Message" class="lg:col-span-2">
            <div class="text-sm leading-relaxed whitespace-pre-line text-slate-700">{{ $item->message }}</div>
        </x-admin.card>
        <x-admin.card title="Sender">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs text-slate-500">Name</dt><dd class="font-medium">{{ $item->name }}</dd></div>
                <div><dt class="text-xs text-slate-500">Email</dt><dd class="font-medium">{{ $item->email ?: '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Phone</dt><dd class="font-medium">{{ $item->phone ?: '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">IP address</dt><dd class="font-medium">{{ $item->ip_address ?: '—' }}</dd></div>
            </dl>
            <button type="button" class="a-btn-ghost a-btn-sm mt-5 text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.messages.destroy', $item)), title: 'Delete this message?' })"><x-icon name="trash" class="size-3.5" /> Delete</button>
        </x-admin.card>
    </div>
@endsection
