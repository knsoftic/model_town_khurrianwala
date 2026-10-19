@extends('layouts.admin')

@section('title', $lead->name)

@section('breadcrumbs')
    <a href="{{ route('admin.leads.index') }}" class="hover:text-slate-900">Leads</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $lead->name }}</span>
@endsection

@php
    $digits = preg_replace('/\D/', '', $lead->phone);
    $wa = 'https://wa.me/'.$digits.'?text='.rawurlencode('Assalam o Alaikum '.$lead->name.', this is '.auth()->user()->name.' from '.setting('site_name').' regarding your plot inquiry.');
@endphp

@section('content')
    <x-admin.page-header :title="$lead->name" :description="$lead->source->label().' · received '.$lead->created_at->format('d M Y, h:i A').' ('.$lead->created_at->diffForHumans().')'">
        <x-slot:actions>
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $lead->phone) }}" class="a-btn-secondary"><x-icon name="phone" class="size-4" /> Call</a>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="a-btn bg-emerald-600 text-white hover:bg-emerald-500"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
            @if ($lead->email)
                <a href="mailto:{{ $lead->email }}" class="a-btn-secondary"><x-icon name="mail" class="size-4" /> Email</a>
            @endif
            <button type="button" class="a-btn-ghost text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.leads.destroy', $lead)), title: 'Delete this lead?' })"><x-icon name="trash" class="size-4" /></button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-admin.card title="Inquiry">
                <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-3">
                    @foreach ([
                        'Phone' => $lead->phone, 'Email' => $lead->email, 'Plot size' => $lead->plot?->size_label,
                        'Budget' => $lead->budget, 'Payment preference' => $lead->paymentPreferenceLabel(), 'Contact by' => $lead->contactMethodLabel(),
                        'Preferred time' => $lead->preferred_time, 'Source' => $lead->source->label(), 'Last contacted' => $lead->last_contacted_at?->diffForHumans(),
                    ] as $label => $value)
                        <div><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="mt-0.5 font-medium text-slate-900">{{ $value ?: '—' }}</dd></div>
                    @endforeach
                </dl>
                @if ($lead->message)
                    <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm leading-relaxed text-slate-700">{!! nl2br(e($lead->message)) !!}</div>
                @endif
                <p class="mt-4 text-xs text-slate-400">
                    @if ($lead->page_url)Page: <span class="break-all">{{ $lead->page_url }}</span> · @endif IP: {{ $lead->ip_address ?? '—' }}
                </p>
            </x-admin.card>

            <x-admin.card title="Notes & follow-up history" :padding="false">
                <form method="POST" action="{{ route('admin.leads.notes.store', $lead) }}" class="border-b border-slate-100 p-5">
                    @csrf
                    <x-admin.textarea name="body" rows="2" placeholder="Write a note — what was discussed, next step…" />
                    <div class="mt-2 flex justify-end"><button class="a-btn-primary a-btn-sm"><x-icon name="message-square-plus" class="size-3.5" /> Add note</button></div>
                </form>
                <ol class="divide-y divide-slate-100">
                    @forelse ($lead->notes as $note)
                        <li class="flex gap-3 px-5 py-4">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-navy-900 text-[11px] font-semibold text-gold-400">{{ $note->user?->initials() ?? '?' }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-slate-500"><span class="font-semibold text-slate-800">{{ $note->user?->name ?? 'Deleted user' }}</span> · {{ $note->created_at->format('d M Y, h:i A') }}</p>
                                <p class="mt-1 text-sm whitespace-pre-line text-slate-700">{{ $note->body }}</p>
                            </div>
                            @if ($note->user_id === auth()->id() || auth()->user()->isSuperAdmin())
                                <button type="button" class="a-icon-btn" title="Delete note" @click="$store.confirm.ask({ action: @js(route('admin.notes.destroy', $note)), title: 'Delete this note?' })"><x-icon name="trash" class="size-3.5" /></button>
                            @endif
                        </li>
                    @empty
                        <li class="px-5 py-8 text-center text-sm text-slate-500">No notes yet.</li>
                    @endforelse
                </ol>
            </x-admin.card>

            <form method="POST" action="{{ route('admin.leads.update', $lead) }}" x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">
                @csrf @method('PUT')
                <x-admin.card title="Edit details">
                    <x-slot:actions><button type="button" class="a-btn-ghost a-btn-sm" @click="open = !open" x-text="open ? 'Close' : 'Edit'"></button></x-slot:actions>
                    <div x-show="open" x-collapse>
                        @include('admin.leads._fields')
                        <div class="mt-5 flex justify-end"><button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save details</button></div>
                    </div>
                    <p x-show="!open" class="text-sm text-slate-500">Change name, phone, plot size, source or message.</p>
                </x-admin.card>
            </form>
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.leads.quick', $lead) }}">
                @csrf @method('PATCH')
                <x-admin.card title="Manage">
                    <div class="mb-4"><span class="a-badge {{ $lead->status->badgeClasses() }}">{{ $lead->status->label() }}</span></div>
                    <div class="space-y-4">
                        <x-admin.select name="status" label="Status" :value="$lead->status" :options="\App\Enums\LeadStatus::options()" />
                        <x-admin.select name="assigned_to" label="Assigned salesperson" :value="$lead->assigned_to" :options="$staff" placeholder="Unassigned" />
                        <x-admin.input name="follow_up_date" type="date" label="Follow-up date" :value="$lead->follow_up_date?->format('Y-m-d')" />
                    </div>
                    <button class="a-btn-primary mt-5 w-full"><x-icon name="check" class="size-4" /> Update</button>
                </x-admin.card>
            </form>

            @if ($lead->bookingRequest)
                <x-admin.card title="Booking request">
                    <a href="{{ route('admin.bookings.show', $lead->bookingRequest) }}" class="block rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                        <p class="font-mono text-sm font-semibold text-slate-900">{{ $lead->bookingRequest->reference }}</p>
                        <p class="mt-1"><span class="a-badge {{ $lead->bookingRequest->status->badgeClasses() }}">{{ $lead->bookingRequest->status->label() }}</span></p>
                    </a>
                </x-admin.card>
            @endif
        </div>
    </div>
@endsection
