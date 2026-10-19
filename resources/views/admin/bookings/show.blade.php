@extends('layouts.admin')

@section('title', 'Booking '.$booking->reference)

@section('breadcrumbs')
    <a href="{{ route('admin.bookings.index') }}" class="hover:text-slate-900">Booking Requests</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $booking->reference }}</span>
@endsection

@php
    $digits = preg_replace('/\D/', '', $booking->phone);
    $wa = 'https://wa.me/'.$digits.'?text='.rawurlencode('Assalam o Alaikum '.$booking->name.', this is '.auth()->user()->name.' from '.setting('site_name').' regarding your booking request '.$booking->reference.'.');
    $actions = [
        [\App\Enums\BookingStatus::Approved, 'Approve', 'circle-check', 'bg-emerald-600 text-white hover:bg-emerald-500'],
        [\App\Enums\BookingStatus::OnHold, 'Put on hold', 'circle-pause', 'bg-amber-500 text-white hover:bg-amber-400'],
        [\App\Enums\BookingStatus::Contacted, 'Mark contacted', 'phone-outgoing', 'bg-indigo-600 text-white hover:bg-indigo-500'],
        [\App\Enums\BookingStatus::Rejected, 'Reject', 'circle-x', 'bg-rose-600 text-white hover:bg-rose-500'],
    ];
@endphp

@section('content')
    <x-admin.page-header :title="$booking->name" :description="'Booking request '.$booking->reference.' · received '.$booking->created_at->format('d M Y, h:i A')">
        <x-slot:actions>
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $booking->phone) }}" class="a-btn-secondary"><x-icon name="phone" class="size-4" /> Call</a>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="a-btn bg-emerald-600 text-white hover:bg-emerald-500"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
            @if ($booking->email)<a href="mailto:{{ $booking->email }}?subject={{ rawurlencode('Your booking request '.$booking->reference) }}" class="a-btn-secondary"><x-icon name="mail" class="size-4" /> Email</a>@endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-6 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-white p-4">
        <span class="mr-2 text-sm text-slate-600">Status: <span class="a-badge ml-1 {{ $booking->status->badgeClasses() }}">{{ $booking->status->label() }}</span></span>
        @foreach ($actions as [$status, $label, $icon, $classes])
            @unless ($booking->status === $status)
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ $status->value }}">
                    <button class="a-btn a-btn-sm {{ $classes }}"><x-icon :name="$icon" class="size-3.5" /> {{ $label }}</button>
                </form>
            @endunless
        @endforeach
        @if ($booking->reviewer)
            <span class="ml-auto text-xs text-slate-500">Last change by {{ $booking->reviewer->name }} · {{ $booking->reviewed_at?->diffForHumans() }}</span>
        @endif
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-admin.card title="Customer & request">
                <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-xs text-slate-500">Phone</dt><dd class="mt-0.5 font-medium">{{ $booking->phone }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Email</dt><dd class="mt-0.5 font-medium">{{ $booking->email ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">City</dt><dd class="mt-0.5 font-medium">{{ $booking->city ?: '—' }}</dd></div>
                    <div x-data="{ shown: false }">
                        <dt class="text-xs text-slate-500">CNIC</dt>
                        <dd class="mt-0.5 font-mono font-medium">
                            @if ($booking->cnic)
                                <span x-show="!shown">{{ $booking->maskedCnic() }}</span>
                                <span x-cloak x-show="shown">{{ $booking->cnic }}</span>
                                <button type="button" class="ml-1 text-xs font-semibold text-gold-700 hover:underline" @click="shown = !shown" x-text="shown ? 'Hide' : 'Show'"></button>
                            @else — @endif
                        </dd>
                    </div>
                    <div><dt class="text-xs text-slate-500">Contact by</dt><dd class="mt-0.5 font-medium">{{ \App\Models\Lead::CONTACT_METHODS[$booking->preferred_contact_method] ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Plot size</dt><dd class="mt-0.5 font-medium">{{ $booking->plot?->size_label ?? '—' }}</dd></div>
                </dl>
                @if ($booking->message)
                    <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-700">{!! nl2br(e($booking->message)) !!}</div>
                @endif
                <p class="mt-4 text-xs text-slate-400">IP: {{ $booking->ip_address ?? '—' }}</p>
            </x-admin.card>

            @if ($booking->installmentPlan)
                @php($s = $booking->installmentPlan->summary())
                <x-admin.card :title="'Selected plan: '.$booking->installmentPlan->name" :padding="false">
                    <div class="overflow-x-auto">
                    <table class="a-table">
                        <thead><tr><th>#</th><th>Stage</th><th>Due</th><th class="text-right">Amount</th></tr></thead>
                        <tbody>
                            @foreach ($s->stages as $i => $stage)
                                <tr>
                                    <td class="text-xs text-slate-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="font-medium">{{ $stage['item']->title }}</td>
                                    <td class="text-xs text-slate-500">{{ $stage['item']->due_label }}</td>
                                    <td class="text-right whitespace-nowrap">{{ money($stage['item']->amount, false) }}@if ($stage['item']->occurrences > 1) <span class="text-xs text-slate-500">× {{ $stage['item']->occurrences }}</span>@endif</td>
                                </tr>
                            @endforeach
                            <tr class="bg-slate-50"><td colspan="3" class="font-semibold">Total</td><td class="text-right font-bold">{{ money($s->totalPrice, false) }}</td></tr>
                        </tbody>
                    </table>
                    </div>
                </x-admin.card>
            @endif

            <x-admin.card title="Notes" :padding="false">
                <form method="POST" action="{{ route('admin.bookings.notes.store', $booking) }}" class="border-b border-slate-100 p-5">
                    @csrf
                    <x-admin.textarea name="body" rows="2" placeholder="Documents received, call summary, next step…" />
                    <div class="mt-2 flex justify-end"><button class="a-btn-primary a-btn-sm"><x-icon name="message-square-plus" class="size-3.5" /> Add note</button></div>
                </form>
                <ol class="divide-y divide-slate-100">
                    @forelse ($booking->notes as $note)
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
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                @csrf @method('PUT')
                <x-admin.card title="Assignment">
                    <x-admin.select name="assigned_to" label="Handled by" :value="$booking->assigned_to" :options="$staff" placeholder="Unassigned" />
                    <button class="a-btn-primary mt-4 w-full"><x-icon name="check" class="size-4" /> Save</button>
                </x-admin.card>
            </form>

            @if ($booking->lead)
                <x-admin.card title="Linked lead">
                    <a href="{{ route('admin.leads.show', $booking->lead) }}" class="flex items-center justify-between rounded-lg border border-slate-200 p-4 hover:bg-slate-50">
                        <span><span class="block font-semibold text-slate-900">{{ $booking->lead->name }}</span><span class="text-xs text-slate-500">Lead status</span></span>
                        <span class="a-badge {{ $booking->lead->status->badgeClasses() }}">{{ $booking->lead->status->label() }}</span>
                    </a>
                    <p class="mt-3 text-xs text-slate-500">Approving marks the lead “Booked”; rejecting marks it “Cancelled”.</p>
                </x-admin.card>
            @endif

            <x-admin.card title="Danger zone">
                <button type="button" class="a-btn-ghost a-btn-sm text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.bookings.destroy', $booking)), title: 'Delete this booking request?' })"><x-icon name="trash" class="size-3.5" /> Delete request</button>
            </x-admin.card>
        </div>
    </div>
@endsection
