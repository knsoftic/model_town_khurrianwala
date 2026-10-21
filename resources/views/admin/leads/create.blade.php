@extends('layouts.admin')

@section('title', 'Add Lead')

@section('breadcrumbs')
    <a href="{{ route('admin.leads.index') }}" class="hover:text-slate-900">Leads</a> <x-icon name="chevron-right" class="size-3" /> <span>New</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.leads.store') }}">
        @csrf
        <x-admin.page-header title="Add Lead" description="Record a lead from a Facebook ad, WhatsApp message, phone call or walk-in, so it can be followed up." />

        <div class="grid max-w-5xl gap-6">
            <x-admin.card title="Lead details">
                @include('admin.leads._fields')
                <p class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                    <x-icon name="clock" class="size-3.5" />
                    {{ auth()->user()->can('leads')
                        ? 'Date and time are saved automatically when you click “Add Lead”.'
                        : 'Date and time are saved automatically when you click “Add Lead”, and the lead is added to your own list.' }}
                </p>
            </x-admin.card>

            <x-admin.card title="First follow-up" description="Optional — when to call or message this person next. It shows on your follow-up list until you mark it done.">
                <div class="grid gap-4 sm:grid-cols-[1fr_1fr_2fr]">
                    <x-admin.input name="next_follow_up_date" type="date" label="Date" :min="today()->format('Y-m-d')" />
                    <x-admin.input name="next_follow_up_time" type="time" label="Time (optional)" />
                    <x-admin.input name="next_follow_up_note" label="What to do" placeholder="e.g. Send 5 Marla payment plan on WhatsApp" maxlength="500" />
                </div>
            </x-admin.card>
        </div>

        <x-admin.form-actions :cancel="route('admin.leads.index')" label="Add Lead" />
    </form>
@endsection
