@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
    <x-admin.page-header title="Reports" description="Open a report to filter it, then export to CSV, Excel or PDF." />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($reports as $key => [$title, $description, $icon])
            <a href="{{ route('admin.reports.show', $key) }}" class="a-card group flex items-start gap-4 p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-navy-900 text-gold-400 transition group-hover:bg-gold-500 group-hover:text-navy-900"><x-icon :name="$icon" class="size-6" /></span>
                <span>
                    <span class="block font-semibold text-slate-900">{{ $title }}</span>
                    <span class="mt-1 block text-sm text-slate-500">{{ $description }}</span>
                </span>
            </a>
        @endforeach
    </div>
@endsection
