@extends('layouts.admin')

@section('title', 'Homepage Sections')

@php
    $icons = [
        'hero' => 'panel-top', 'intro' => 'house', 'plots' => 'land-plot', 'installment_plans' => 'calendar-range', 'calculator' => 'calculator',
        'features' => 'sparkles', 'location' => 'map-pin', 'development' => 'hard-hat', 'testimonials' => 'quote', 'gallery' => 'images',
        'video' => 'clapperboard', 'faq' => 'circle-question-mark', 'contact_cta' => 'phone-call', 'custom' => 'layout-template',
    ];
@endphp

@section('content')
    <x-admin.page-header title="Homepage Sections" description="Drag to reorder, switch sections on or off, and edit each section's text, buttons and images. Changes are live immediately.">
        <x-slot:actions>
            <a href="{{ route('home') }}" target="_blank" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> View homepage</a>
            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                <button type="button" class="a-btn-gold" @click="open = !open"><x-icon name="plus" class="size-4" /> Add section</button>
                <div x-cloak x-show="open" x-transition class="absolute right-0 z-20 mt-2 max-h-96 w-64 overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                    @foreach (\App\Models\HomepageSection::TYPES as $type => $label)
                        <a href="{{ route('admin.sections.create', ['type' => $type]) }}" class="flex items-center gap-2.5 px-4 py-2 text-sm hover:bg-slate-50">
                            <x-icon :name="$icons[$type] ?? 'square'" class="size-4 text-slate-400" /> {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <ol class="space-y-2" x-data="sortable(@js(route('admin.reorder', 'sections')))">
        @foreach ($sections as $section)
            <li data-id="{{ $section->id }}" class="a-card flex items-center gap-4 p-3 pr-4">
                <span data-drag class="cursor-grab px-1 text-slate-400 hover:text-slate-700" title="Drag to reorder"><x-icon name="grip-vertical" class="size-5" /></span>
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-navy-900 text-gold-400"><x-icon :name="$icons[$section->type] ?? 'square'" class="size-5" /></span>
                <div class="min-w-0 flex-1">
                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-semibold text-slate-900">
                        {{ $section->name }}
                        <span class="a-badge bg-slate-100 text-slate-600 ring-slate-300">{{ $section->typeLabel() }}</span>
                    </p>
                    <p class="truncate text-xs text-slate-500">{{ $section->heading ?: 'No heading' }}</p>
                </div>
                <x-admin.switch :url="route('admin.toggle', ['sections', $section->id])" :value="$section->is_active" label="Show on homepage" />
                <x-admin.row-actions :edit="route('admin.sections.edit', $section)" :delete="route('admin.sections.destroy', $section)" :delete-label="$section->name" />
            </li>
        @endforeach
    </ol>
    @if ($sections->isEmpty())
        <x-admin.card><x-admin.empty icon="layout-template" title="The homepage has no sections" description="Add a section to start building the homepage." /></x-admin.card>
    @endif
@endsection
