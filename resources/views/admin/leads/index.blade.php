@extends('layouts.admin')

@section('title', 'Leads')

@php
    $current = request('status');
    $followUpFilter = request('follow_up');
    $tiles = [
        'overdue' => ['Overdue follow-ups', 'alarm-clock', 'border-rose-200 bg-rose-50 text-rose-700'],
        'today' => ['Follow-ups today', 'bell-ring', 'border-amber-200 bg-amber-50 text-amber-800'],
        'upcoming' => ['Upcoming follow-ups', 'calendar-check', 'border-sky-200 bg-sky-50 text-sky-800'],
        'none' => ['No follow-up planned', 'calendar-x', 'border-slate-200 bg-white text-slate-700'],
    ];
@endphp

@section('content')
    <x-admin.page-header title="Leads" :description="$canManage ? 'Every inquiry, callback and booking from the website — plus leads added by your team.' : 'Your leads: the ones you added and the ones assigned to you.'">
        <x-slot:actions>
            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                <button type="button" class="a-btn-secondary" @click="open = !open"><x-icon name="download" class="size-4" /> Export</button>
                <div x-cloak x-show="open" x-transition class="absolute right-0 z-20 mt-2 w-48 rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                    <a href="{{ route('admin.leads.export', ['csv'] + request()->query()) }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"><x-icon name="file-text" class="size-4" /> CSV</a>
                    <a href="{{ route('admin.leads.export', ['xlsx'] + request()->query()) }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"><x-icon name="file-spreadsheet" class="size-4" /> Excel (.xlsx)</a>
                    <a href="{{ route('admin.leads.export', ['docx'] + request()->query()) }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"><x-icon name="file-type" class="size-4" /> Word (.docx)</a>
                </div>
            </div>
            <a href="{{ route('admin.leads.create') }}" class="a-btn-gold"><x-icon name="user-plus" class="size-4" /> Add Lead</a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Follow-up summary --}}
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($tiles as $key => [$label, $icon, $classes])
            <a href="{{ request()->fullUrlWithQuery(['follow_up' => $followUpFilter === $key ? null : $key, 'page' => null]) }}"
               @class(['flex items-center gap-3 rounded-xl border p-4 transition hover:shadow-card', $classes, 'ring-2 ring-navy-900' => $followUpFilter === $key])>
                <x-icon :name="$icon" class="size-5 shrink-0" />
                <span class="min-w-0">
                    <span class="block text-2xl leading-none font-bold">{{ $followUpCounts[$key] }}</span>
                    <span class="mt-1 block truncate text-xs font-medium">{{ $label }}</span>
                </span>
            </a>
        @endforeach
    </div>

    {{-- Status tabs --}}
    <div class="scrollbar-none mb-4 flex gap-2 overflow-x-auto pb-1">
        <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" @class(['a-btn a-btn-sm shrink-0 border', 'border-navy-900 bg-navy-900 text-white' => ! $current, 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $current])>All <span class="opacity-70">{{ $counts->sum() }}</span></a>
        @foreach (\App\Enums\LeadStatus::cases() as $status)
            <a href="{{ request()->fullUrlWithQuery(['status' => $status->value, 'page' => null]) }}" @class(['a-btn a-btn-sm shrink-0 border', 'border-navy-900 bg-navy-900 text-white' => $current === $status->value, 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $current !== $status->value])>
                {{ $status->label() }} <span class="opacity-70">{{ $counts[$status->value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-[1.4fr_1fr_1fr_1fr_1fr_auto]">
        @if ($current)<input type="hidden" name="status" value="{{ $current }}">@endif
        <x-admin.input name="q" :value="request('q')" placeholder="Name, phone or email…" />
        <x-admin.select name="source" :value="request('source')" :options="\App\Enums\LeadSource::options()" placeholder="Any source" />
        <x-admin.select name="follow_up" :value="$followUpFilter" :options="['due' => 'Due (today + overdue)', 'overdue' => 'Overdue', 'today' => 'Today', 'upcoming' => 'Upcoming', 'none' => 'No follow-up planned']" placeholder="Any follow-up" />
        @if ($canManage)
            <x-admin.select name="added_by" :value="request('added_by')" :options="$staff" placeholder="Added by anyone" />
            <x-admin.select name="assigned" :value="request('assigned')" :options="['me' => 'Assigned to me', 'none' => 'Unassigned'] + $staff->all()" placeholder="Any salesperson" />
        @else
            <x-admin.select name="plot" :value="request('plot')" :options="$plots" placeholder="Any plot size" />
        @endif
        <div class="flex gap-2"><button class="a-btn-primary"><x-icon name="search" class="size-4" /> Filter</button>@if (request()->except('page'))<a href="{{ route('admin.leads.index') }}" class="a-btn-ghost">Reset</a>@endif</div>
        <details class="sm:col-span-2 lg:col-span-4 xl:col-span-6" @if (request('from') || request('to') || ($canManage && request('plot'))) open @endif>
            <summary class="cursor-pointer text-xs font-semibold text-slate-500">More filters</summary>
            <div class="mt-2 grid max-w-2xl gap-3 sm:grid-cols-3">
                <x-admin.input name="from" type="date" label="Received from" :value="request('from')" />
                <x-admin.input name="to" type="date" label="Received to" :value="request('to')" />
                @if ($canManage)
                    <x-admin.select name="plot" label="Plot size" :value="request('plot')" :options="$plots" placeholder="Any plot size" />
                @endif
            </div>
        </details>
    </form>

    <form method="POST" action="{{ $canManage ? route('admin.leads.bulk') : '#' }}" x-data="bulkSelect" class="relative">
        @csrf
        @if ($canManage)
            <div x-cloak x-show="selected.length" x-transition class="a-card mb-3 flex flex-wrap items-center gap-2 border-gold-300 bg-gold-50 p-3 text-sm">
                <span class="font-semibold text-slate-800"><span x-text="selected.length"></span> selected</span>
                <select name="status" class="a-input w-auto py-1.5 text-sm">
                    <option value="">Change status…</option>
                    @foreach (\App\Enums\LeadStatus::options() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </select>
                <button name="action" value="status" class="a-btn-secondary a-btn-sm">Apply</button>
                <select name="assigned_to" class="a-input w-auto py-1.5 text-sm">
                    <option value="">Unassign</option>
                    @foreach ($staff as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                </select>
                <button name="action" value="assign" class="a-btn-secondary a-btn-sm">Assign</button>
                <button name="action" value="delete" class="a-btn-danger a-btn-sm ml-auto" onclick="return confirm('Delete the selected leads?')"><x-icon name="trash" class="size-3.5" /> Delete</button>
            </div>
        @endif

        <x-admin.card :padding="false">
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            @if ($canManage)
                                <th class="w-8"><input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300 text-navy-900 focus:ring-gold-500" aria-label="Select all"></th>
                            @endif
                            <th>Lead</th>
                            @foreach ($listFields as $field)<th>{{ $field->label }}</th>@endforeach
                            <th>Source</th><th>Status</th>
                            @if ($canManage)<th>Salesperson</th>@endif
                            <th>Next follow-up</th><th>Last done</th><th>Received</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            @php($next = $lead->nextFollowUp)
                            <tr>
                                @if ($canManage)
                                    <td><input type="checkbox" name="ids[]" value="{{ $lead->id }}" data-row-id x-model="selected" class="rounded border-slate-300 text-navy-900 focus:ring-gold-500" aria-label="Select {{ $lead->name }}"></td>
                                @endif
                                <td>
                                    <a href="{{ route('admin.leads.show', $lead) }}" class="font-semibold text-slate-900 hover:text-gold-700">{{ $lead->name }}</a>
                                    <span class="block text-xs text-slate-500">{{ $lead->phone }}@if ($lead->plot) · {{ $lead->plot->size_label }}@endif</span>
                                    @if ($canManage && $lead->creator)
                                        <span class="block text-[11px] text-slate-400">Added by {{ $lead->creator->name }}</span>
                                    @endif
                                </td>
                                @foreach ($listFields as $field)
                                    <td class="max-w-48 truncate text-xs text-slate-600" title="{{ $field->display($lead) }}">{{ $field->display($lead) ?? '—' }}</td>
                                @endforeach
                                <td class="whitespace-nowrap text-xs text-slate-600">{{ $lead->source->label() }}</td>
                                <td x-data="inlineSelect(@js(route('admin.leads.quick', $lead)), 'status')">
                                    <select @change="save($event)" class="a-input w-32 py-1 text-xs" aria-label="Status">
                                        @foreach (\App\Enums\LeadStatus::options() as $value => $label)
                                            <option value="{{ $value }}" @selected($lead->status->value === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                @if ($canManage)
                                    <td x-data="inlineSelect(@js(route('admin.leads.quick', $lead)), 'assigned_to')">
                                        <select @change="save($event)" class="a-input w-32 py-1 text-xs" aria-label="Salesperson">
                                            <option value="">Unassigned</option>
                                            @foreach ($staff as $id => $name)<option value="{{ $id }}" @selected((int) $lead->assigned_to === (int) $id)>{{ $name }}</option>@endforeach
                                        </select>
                                    </td>
                                @endif
                                <td class="whitespace-nowrap text-xs">
                                    @if ($next)
                                        @php($state = $next->state())
                                        <span @class(['font-semibold', 'text-rose-600' => $state === 'overdue', 'text-amber-700' => $state === 'today', 'text-slate-700' => $state === 'upcoming'])>{{ $next->dueLabel() }}</span>
                                        <span class="block text-[11px] text-slate-400">{{ ['overdue' => 'Overdue', 'today' => 'Today', 'upcoming' => 'Planned'][$state] }}</span>
                                    @else
                                        <span class="text-slate-400">Not planned</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-xs text-slate-600">
                                    @if ($lead->lastFollowUp)
                                        <span class="inline-flex items-center gap-1"><x-icon name="circle-check" class="size-3.5 text-emerald-600" /> {{ $lead->lastFollowUp->done_at->format('d M Y') }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-xs text-slate-500" title="{{ $lead->created_at->diffForHumans() }}">{{ $lead->created_at->format('d M Y') }}<span class="block text-[11px] text-slate-400">{{ $lead->created_at->format('h:i A') }}</span></td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}" target="_blank" rel="noopener" class="a-icon-btn text-emerald-600" title="WhatsApp"><x-icon name="whatsapp" class="size-4" /></a>
                                        <a href="{{ route('admin.leads.show', $lead) }}" class="a-icon-btn" title="Open"><x-icon name="eye" class="size-4" /></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 8 + $listFields->count() + ($canManage ? 2 : 0) }}"><x-admin.empty icon="users-round" title="No leads found" description="Try a different filter, or add a lead with “Add Lead”." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </form>
    <div class="mt-4">{{ $leads->links() }}</div>
@endsection
