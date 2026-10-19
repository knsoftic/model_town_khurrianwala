@extends('layouts.admin')

@section('title', 'Leads')

@section('content')
    <x-admin.page-header title="Leads" description="Every inquiry, callback and booking from the website — plus leads you add yourself.">
        <x-slot:actions>
            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                <button type="button" class="a-btn-secondary" @click="open = !open"><x-icon name="download" class="size-4" /> Export</button>
                <div x-cloak x-show="open" x-transition class="absolute right-0 z-20 mt-2 w-44 rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                    <a href="{{ route('admin.leads.export', ['csv'] + request()->query()) }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"><x-icon name="file-text" class="size-4" /> CSV</a>
                    <a href="{{ route('admin.leads.export', ['xlsx'] + request()->query()) }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"><x-icon name="file-spreadsheet" class="size-4" /> Excel (.xlsx)</a>
                </div>
            </div>
            <a href="{{ route('admin.leads.create') }}" class="a-btn-gold"><x-icon name="user-plus" class="size-4" /> Add Lead</a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Status tabs --}}
    <div class="scrollbar-none mb-4 flex gap-2 overflow-x-auto pb-1">
        @php($current = request('status'))
        <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" @class(['a-btn a-btn-sm shrink-0 border', 'border-navy-900 bg-navy-900 text-white' => ! $current, 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $current])>All <span class="opacity-70">{{ $total }}</span></a>
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
        <x-admin.select name="plot" :value="request('plot')" :options="$plots" placeholder="Any plot size" />
        <x-admin.select name="assigned" :value="request('assigned')" :options="['me' => 'Assigned to me', 'none' => 'Unassigned'] + $staff->all()" placeholder="Anyone" />
        <x-admin.select name="follow_up" :value="request('follow_up')" :options="['due' => 'Follow-up due', 'upcoming' => 'Follow-up upcoming']" placeholder="Any follow-up" />
        <div class="flex gap-2"><button class="a-btn-primary"><x-icon name="search" class="size-4" /> Filter</button>@if (request()->except('page'))<a href="{{ route('admin.leads.index') }}" class="a-btn-ghost">Reset</a>@endif</div>
        <details class="sm:col-span-2 lg:col-span-4 xl:col-span-6" @if (request('from') || request('to')) open @endif>
            <summary class="cursor-pointer text-xs font-semibold text-slate-500">Date range</summary>
            <div class="mt-2 grid max-w-md grid-cols-2 gap-3">
                <x-admin.input name="from" type="date" label="From" :value="request('from')" />
                <x-admin.input name="to" type="date" label="To" :value="request('to')" />
            </div>
        </details>
    </form>

    <form method="POST" action="{{ route('admin.leads.bulk') }}" x-data="bulkSelect" class="relative">
        @csrf
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

        <x-admin.card :padding="false">
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th class="w-8"><input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300 text-navy-900 focus:ring-gold-500" aria-label="Select all"></th>
                            <th>Lead</th><th>Plot</th><th>Source</th><th>Status</th><th>Assigned</th><th>Follow-up</th><th>Received</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $lead->id }}" data-row-id x-model="selected" class="rounded border-slate-300 text-navy-900 focus:ring-gold-500" aria-label="Select {{ $lead->name }}"></td>
                                <td>
                                    <a href="{{ route('admin.leads.show', $lead) }}" class="font-semibold text-slate-900 hover:text-gold-700">{{ $lead->name }}</a>
                                    <span class="block text-xs text-slate-500">{{ $lead->phone }}@if ($lead->email) · {{ $lead->email }}@endif</span>
                                </td>
                                <td class="whitespace-nowrap">{{ $lead->plot?->size_label ?? '—' }}</td>
                                <td class="whitespace-nowrap text-xs text-slate-600">{{ $lead->source->label() }}</td>
                                <td x-data="inlineSelect(@js(route('admin.leads.quick', $lead)), 'status')">
                                    <select @change="save($event)" class="a-input w-36 py-1 text-xs" aria-label="Status">
                                        @foreach (\App\Enums\LeadStatus::options() as $value => $label)
                                            <option value="{{ $value }}" @selected($lead->status->value === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td x-data="inlineSelect(@js(route('admin.leads.quick', $lead)), 'assigned_to')">
                                    <select @change="save($event)" class="a-input w-36 py-1 text-xs" aria-label="Assigned to">
                                        <option value="">Unassigned</option>
                                        @foreach ($staff as $id => $name)<option value="{{ $id }}" @selected((int) $lead->assigned_to === (int) $id)>{{ $name }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="whitespace-nowrap text-xs">
                                    @if ($lead->follow_up_date)
                                        <span @class(['font-semibold', 'text-rose-600' => $lead->isFollowUpDue(), 'text-slate-600' => ! $lead->isFollowUpDue()])>{{ $lead->follow_up_date->format('d M Y') }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-xs text-slate-500" title="{{ $lead->created_at->format('d M Y, h:i A') }}">{{ $lead->created_at->diffForHumans() }}</td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}" target="_blank" rel="noopener" class="a-icon-btn text-emerald-600" title="WhatsApp"><x-icon name="whatsapp" class="size-4" /></a>
                                        <a href="{{ route('admin.leads.show', $lead) }}" class="a-icon-btn" title="Open"><x-icon name="eye" class="size-4" /></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><x-admin.empty icon="users-round" title="No leads found" description="Try a different filter, or add a lead manually." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </form>
    <div class="mt-4">{{ $leads->links() }}</div>
@endsection
