@extends('layouts.admin')

@section('title', 'FAQs')

@section('content')
    <x-admin.page-header title="FAQs" description="Questions shown on the FAQ page (grouped by category), the homepage and plot pages. Drag to reorder.">
        <x-slot:actions><a href="{{ route('admin.faqs.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add FAQ</a></x-slot:actions>
    </x-admin.page-header>

    <ol class="space-y-2" x-data="sortable(@js(route('admin.reorder', 'faqs')))">
        @forelse ($items as $item)
            <li data-id="{{ $item->id }}" class="a-card flex items-start gap-3 p-4" x-data="{ open: false }">
                <span data-drag class="mt-0.5 cursor-grab text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-5" /></span>
                <div class="min-w-0 flex-1">
                    <button type="button" class="flex w-full items-start gap-2 text-left" @click="open = !open">
                        <span class="flex-1 font-semibold text-slate-900">{{ $item->question }}</span>
                        @if ($item->category)<span class="a-badge bg-slate-100 text-slate-600 ring-slate-300">{{ $item->category }}</span>@endif
                    </button>
                    <p x-show="open" x-collapse class="mt-2 text-sm whitespace-pre-line text-slate-600">{{ $item->answer }}</p>
                </div>
                <x-admin.switch :url="route('admin.toggle', ['faqs', $item->id])" :value="$item->is_active" />
                <x-admin.row-actions :edit="route('admin.faqs.edit', $item->id)" :delete="route('admin.faqs.destroy', $item->id)" delete-label="this FAQ" />
            </li>
        @empty
            <li class="a-card"><x-admin.empty icon="circle-question-mark" title="No FAQs yet" /></li>
        @endforelse
    </ol>
@endsection
