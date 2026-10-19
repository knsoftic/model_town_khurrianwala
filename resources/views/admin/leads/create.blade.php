@extends('layouts.admin')

@section('title', 'Add Lead')

@section('breadcrumbs')
    <a href="{{ route('admin.leads.index') }}" class="hover:text-slate-900">Leads</a> <x-icon name="chevron-right" class="size-3" /> <span>New</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.leads.store') }}">
        @csrf
        <x-admin.page-header title="Add Lead" description="Record a walk-in, phone call or referral so it can be followed up like website leads." />
        <x-admin.card class="max-w-4xl">
            @include('admin.leads._fields')
        </x-admin.card>
        <x-admin.form-actions :cancel="route('admin.leads.index')" label="Add Lead" />
    </form>
@endsection
