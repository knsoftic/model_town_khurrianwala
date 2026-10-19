@extends('layouts.admin')

@section('title', $item->exists ? 'Edit FAQ' : 'Add FAQ')

@section('breadcrumbs')
    <a href="{{ route('admin.faqs.index') }}" class="hover:text-slate-900">FAQs</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $item->exists ? 'Edit' : 'New' }}</span>
@endsection

@php($categories = \App\Models\Faq::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'))

@section('content')
    <form method="POST" action="{{ $item->exists ? route('admin.faqs.update', $item->id) : route('admin.faqs.store') }}">
        @csrf @if ($item->exists) @method('PUT') @endif
        <x-admin.page-header :title="$item->exists ? 'Edit FAQ' : 'Add FAQ'" />
        <x-admin.card class="max-w-3xl">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="question" label="Question" :value="$item->question" required class="sm:col-span-2" />
                <x-admin.textarea name="answer" label="Answer" :value="$item->answer" rows="5" required class="sm:col-span-2" help="Line breaks are kept." />
                <div>
                    <x-admin.input name="category" label="Category" :value="$item->category" list="faq-categories" placeholder="e.g. Booking" />
                    <datalist id="faq-categories">@foreach ($categories as $category)<option value="{{ $category }}">@endforeach</datalist>
                </div>
                <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$item->sort_order" />
                <x-admin.toggle name="is_active" label="Show on website" :checked="$item->is_active" />
            </div>
        </x-admin.card>
        <x-admin.form-actions :cancel="route('admin.faqs.index')" />
    </form>
@endsection
