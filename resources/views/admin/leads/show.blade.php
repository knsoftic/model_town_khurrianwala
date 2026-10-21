@extends('layouts.admin')

@section('title', $lead->name)

@section('breadcrumbs')
    <a href="{{ route('admin.leads.index') }}" class="hover:text-slate-900">Leads</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $lead->name }}</span>
@endsection

@php
    $digits = preg_replace('/\D/', '', $lead->phone);
    $wa = 'https://wa.me/'.$digits.'?text='.rawurlencode('Assalam o Alaikum '.$lead->name.', this is '.auth()->user()->name.' from '.setting('site_name').' regarding your plot inquiry.');
    $canManage = auth()->user()->can('leads');
    $open = $lead->followUps->reject->isDone();
    $done = $lead->followUps->filter->isDone();
    $stateBadge = [
        'overdue' => ['Overdue', 'bg-rose-100 text-rose-700 ring-rose-600/20'],
        'today' => ['Today', 'bg-amber-100 text-amber-800 ring-amber-600/20'],
        'upcoming' => ['Upcoming', 'bg-sky-100 text-sky-800 ring-sky-600/20'],
    ];
@endphp

@section('content')
    <x-admin.page-header :title="$lead->name" :description="$lead->source->label().' · received '.$lead->created_at->format('d M Y, h:i A').' ('.$lead->created_at->diffForHumans().')'.($lead->creator ? ' · added by '.$lead->creator->name : '')">
        <x-slot:actions>
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $lead->phone) }}" class="a-btn-secondary"><x-icon name="phone" class="size-4" /> Call</a>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="a-btn bg-emerald-600 text-white hover:bg-emerald-500"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
            @if ($lead->email)
                <a href="mailto:{{ $lead->email }}" class="a-btn-secondary"><x-icon name="mail" class="size-4" /> Email</a>
            @endif
            @can('delete', $lead)
                <button type="button" class="a-btn-ghost text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.leads.destroy', $lead)), title: 'Delete this lead?' })"><x-icon name="trash" class="size-4" /></button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-admin.card title="Lead details">
                <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-3">
                    @foreach ($fields->reject(fn ($field) => in_array($field->key, ['name', 'message'], true)) as $field)
                        <div><dt class="text-xs text-slate-500">{{ $field->label }}</dt><dd class="mt-0.5 font-medium break-words text-slate-900">{{ $field->display($lead) ?? '—' }}</dd></div>
                    @endforeach
                    @foreach ([
                        'Lead came from' => $lead->source->label(),
                        'Received' => $lead->created_at->format('d M Y, h:i A'),
                        'Added by' => $lead->creator?->name ?? ($lead->created_by ? 'Deleted user' : 'Website'),
                        'Last contacted' => $lead->last_contacted_at?->format('d M Y, h:i A'),
                    ] as $label => $value)
                        <div><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="mt-0.5 font-medium text-slate-900">{{ $value ?: '—' }}</dd></div>
                    @endforeach
                </dl>
                @if ($lead->message)
                    <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm leading-relaxed text-slate-700">{!! nl2br(e($lead->message)) !!}</div>
                @endif
                @if ($lead->page_url || $lead->ip_address)
                    <p class="mt-4 text-xs text-slate-400">
                        @if ($lead->page_url)Page: <span class="break-all">{{ $lead->page_url }}</span> · @endif IP: {{ $lead->ip_address ?? '—' }}
                    </p>
                @endif
            </x-admin.card>

            <x-admin.card title="Follow-ups" :description="$open->isEmpty() ? 'No follow-up planned. Schedule the next call or message below.' : $open->count().' planned · '.$done->count().' done'" :padding="false">
                <form method="POST" action="{{ route('admin.leads.follow-ups.store', $lead) }}" class="grid gap-3 border-b border-slate-100 p-5 sm:grid-cols-[1fr_1fr_2fr_auto] sm:items-end">
                    @csrf
                    <x-admin.input name="due_date" type="date" label="Follow-up date" :value="today()->addDay()->format('Y-m-d')" required />
                    <x-admin.input name="due_time" type="time" label="Time (optional)" />
                    <x-admin.input name="note" label="What to do" placeholder="e.g. Call back about site visit" maxlength="500" />
                    <button class="a-btn-primary"><x-icon name="calendar-plus" class="size-4" /> Schedule</button>
                </form>

                <ol class="divide-y divide-slate-100">
                    @foreach ($open as $followUp)
                        @php([$stateLabel, $stateClasses] = $stateBadge[$followUp->state()])
                        <li class="px-5 py-4" x-data="{ done: false }">
                            <div class="flex flex-wrap items-start gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-500"><x-icon name="calendar-clock" class="size-4" /></span>
                                <div class="min-w-0 flex-1">
                                    <p class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-900">
                                        {{ $followUp->dueLabel() }} <span class="a-badge {{ $stateClasses }}">{{ $stateLabel }}</span>
                                    </p>
                                    @if ($followUp->note)
                                        <p class="mt-0.5 text-sm text-slate-700">{{ $followUp->note }}</p>
                                    @endif
                                    <p class="mt-0.5 text-xs text-slate-500">Planned by {{ $followUp->user?->name ?? '—' }} · {{ $followUp->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" class="a-btn-gold a-btn-sm" @click="done = !done"><x-icon name="check" class="size-3.5" /> Mark done</button>
                                    <button type="button" class="a-icon-btn" title="Remove follow-up" @click="$store.confirm.ask({ action: @js(route('admin.follow-ups.destroy', $followUp)), title: 'Remove this follow-up?', message: 'Use this only if the follow-up is no longer needed. To record a call, use “Mark done”.', button: 'Remove' })"><x-icon name="trash" class="size-3.5" /></button>
                                </div>
                            </div>
                            <form x-cloak x-show="done" x-collapse method="POST" action="{{ route('admin.follow-ups.update', $followUp) }}" class="mt-3 space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                @csrf @method('PATCH')
                                <x-admin.textarea name="outcome" label="What happened?" rows="2" placeholder="e.g. Called — interested in 5 Marla, will visit on Sunday" />
                                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_2fr]">
                                    <x-admin.input name="next_date" type="date" label="Next follow-up (optional)" :min="today()->format('Y-m-d')" />
                                    <x-admin.input name="next_time" type="time" label="Time" />
                                    <x-admin.input name="next_note" label="Next step" maxlength="500" />
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" class="a-btn-ghost a-btn-sm" @click="done = false">Cancel</button>
                                    <button class="a-btn-primary a-btn-sm"><x-icon name="check" class="size-3.5" /> Save as done</button>
                                </div>
                            </form>
                        </li>
                    @endforeach

                    @foreach ($done as $followUp)
                        <li class="flex items-start gap-3 px-5 py-4">
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"><x-icon name="circle-check" class="size-4" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-900">Done {{ $followUp->done_at->format('d M Y, h:i A') }} <span class="font-normal text-slate-500">by {{ $followUp->doneBy?->name ?? '—' }}</span></p>
                                @if ($followUp->outcome)
                                    <p class="mt-0.5 text-sm whitespace-pre-line text-slate-700">{{ $followUp->outcome }}</p>
                                @endif
                                <p class="mt-0.5 text-xs text-slate-500">Was planned for {{ $followUp->dueLabel() }}@if ($followUp->note) — {{ $followUp->note }}@endif</p>
                            </div>
                        </li>
                    @endforeach

                    @if ($lead->followUps->isEmpty())
                        <li class="px-5 py-8 text-center text-sm text-slate-500">No follow-ups yet.</li>
                    @endif
                </ol>
            </x-admin.card>

            <x-admin.card title="Notes" :padding="false">
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

            @php($detailErrors = collect($errors->keys())->diff(['due_date', 'due_time', 'note', 'outcome', 'next_date', 'next_time', 'next_note', 'body'])->isNotEmpty())
            <form method="POST" action="{{ route('admin.leads.update', $lead) }}" x-data="{ open: {{ $detailErrors ? 'true' : 'false' }} }">
                @csrf @method('PUT')
                <x-admin.card title="Edit details">
                    <x-slot:actions><button type="button" class="a-btn-ghost a-btn-sm" @click="open = !open" x-text="open ? 'Close' : 'Edit'"></button></x-slot:actions>
                    <div x-show="open" x-collapse>
                        @include('admin.leads._fields')
                        <div class="mt-5 flex justify-end"><button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save details</button></div>
                    </div>
                    <p x-show="!open" class="text-sm text-slate-500">Change name, phone, location, source or any other detail.</p>
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
                        @if ($canManage)
                            <x-admin.select name="assigned_to" label="Salesperson / agent" :value="$lead->assigned_to" :options="$staff" placeholder="Unassigned" />
                        @endif
                    </div>
                    <button class="a-btn-primary mt-5 w-full"><x-icon name="check" class="size-4" /> Update</button>
                </x-admin.card>
            </form>

            <x-admin.card title="Next follow-up">
                @if ($next = $open->first())
                    @php([$stateLabel, $stateClasses] = $stateBadge[$next->state()])
                    <p class="text-lg font-semibold text-slate-900">{{ $next->dueLabel() }}</p>
                    <p class="mt-1"><span class="a-badge {{ $stateClasses }}">{{ $stateLabel }}</span></p>
                    @if ($next->note)<p class="mt-2 text-sm text-slate-600">{{ $next->note }}</p>@endif
                @else
                    <p class="text-sm text-slate-500">Nothing planned.</p>
                @endif
                @if ($last = $done->sortByDesc('done_at')->first())
                    <p class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-500">Last follow-up done {{ $last->done_at->format('d M Y, h:i A') }}</p>
                @endif
            </x-admin.card>

            @if ($lead->assignee || $lead->creator)
                <x-admin.card title="People">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-xs text-slate-500">Added by</dt><dd class="font-medium text-slate-900">{{ $lead->creator?->name ?? 'Website' }}</dd></div>
                        <div><dt class="text-xs text-slate-500">Salesperson / agent</dt><dd class="font-medium text-slate-900">{{ $lead->assignee?->name ?? 'Unassigned' }}</dd></div>
                    </dl>
                </x-admin.card>
            @endif

            @if ($lead->bookingRequest && auth()->user()->can('bookings'))
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
