@extends('layouts.admin')

@section('title', 'Lead Form Fields')

@section('content')
    <x-admin.page-header title="Lead Form Fields" description="The fields your team fills in on “Add Lead”. Add your own, rename any field, switch fields on or off, and drag to change the order. Date and time are saved automatically.">
        <x-slot:actions>
            <a href="{{ route('admin.leads.create') }}" class="a-btn-secondary"><x-icon name="eye" class="size-4" /> Preview form</a>
            <a href="{{ route('admin.lead-fields.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Field</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-3 hidden grid-cols-[1.5rem_1fr_7rem_6rem_6rem_6rem_5rem] items-center gap-3 px-4 text-[11px] font-semibold tracking-wide text-slate-500 uppercase md:grid">
        <span></span><span>Field</span><span>Type</span><span class="text-center">On form</span><span class="text-center">Required</span><span class="text-center">In list</span><span></span>
    </div>

    <ol class="space-y-2" x-data="sortable(@js(route('admin.reorder', 'lead-fields')))">
        @foreach ($fields as $field)
            <li data-id="{{ $field->id }}" class="a-card grid grid-cols-[1.5rem_1fr_auto] items-center gap-3 p-4 md:grid-cols-[1.5rem_1fr_7rem_6rem_6rem_6rem_5rem]">
                <span data-drag class="cursor-grab text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-5" /></span>
                <div class="min-w-0">
                    <p class="flex flex-wrap items-center gap-2 font-semibold text-slate-900">
                        {{ $field->label }}
                        @if ($field->is_system)
                            <span class="a-badge bg-slate-100 text-slate-600 ring-slate-300">Built-in</span>
                        @endif
                    </p>
                    @if ($field->type === 'select' && ! $field->is_system && $field->options)
                        <p class="mt-0.5 truncate text-xs text-slate-500">Choices: {{ implode(', ', $field->options) }}</p>
                    @elseif ($field->help)
                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ $field->help }}</p>
                    @endif
                    <p class="mt-0.5 text-xs text-slate-400 md:hidden">{{ $field->typeLabel() }}</p>
                </div>
                <span class="hidden text-xs text-slate-600 md:block">{{ $field->typeLabel() }}</span>
                @if ($field->isLocked())
                    <span class="hidden text-center text-xs text-slate-400 md:block">Always</span>
                    <span class="hidden text-center text-xs text-slate-400 md:block">Always</span>
                    <span class="hidden text-center text-xs text-slate-400 md:block">—</span>
                @else
                    <span class="hidden justify-center md:flex"><x-admin.switch :url="route('admin.toggle', ['lead-fields', $field->id])" :value="$field->is_active" label="Show on the Add Lead form" /></span>
                    <span class="hidden justify-center md:flex"><x-admin.switch :url="route('admin.toggle', ['lead-fields', $field->id])" :value="$field->is_required" field="is_required" label="Required" /></span>
                    <span class="hidden justify-center md:flex">
                        @if ($field->is_system)
                            <span class="text-xs text-slate-400">—</span>
                        @else
                            <x-admin.switch :url="route('admin.toggle', ['lead-fields', $field->id])" :value="$field->show_in_list" field="show_in_list" label="Show as a column in the leads list" />
                        @endif
                    </span>
                @endif
                <x-admin.row-actions :edit="route('admin.lead-fields.edit', $field)" :delete="$field->is_system ? null : route('admin.lead-fields.destroy', $field)" :delete-label="'the field “'.$field->label.'”'" />
            </li>
        @endforeach
    </ol>

    <p class="mt-4 text-xs text-slate-500">
        Name and phone are always on the form. Built-in fields can be renamed or switched off, but not deleted. “Lead came from”, status and the first follow-up are always asked at the bottom of the form.
    </p>
@endsection
