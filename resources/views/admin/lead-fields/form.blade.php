@extends('layouts.admin')

@section('title', $field->exists ? 'Edit Field' : 'Add Field')

@section('breadcrumbs')
    <a href="{{ route('admin.lead-fields.index') }}" class="hover:text-slate-900">Lead Form Fields</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $field->exists ? $field->label : 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $field->exists ? route('admin.lead-fields.update', $field) : route('admin.lead-fields.store') }}"
          x-data="{ type: @js(old('type', $field->type)) }">
        @csrf @if ($field->exists) @method('PUT') @endif
        <x-admin.page-header :title="$field->exists ? 'Edit Field' : 'Add Field'" :description="$field->is_system ? 'A built-in field: you can rename it, add a hint and switch it on or off.' : 'A field on the Add Lead form, e.g. Location, Facebook campaign, Profession.'" />

        <x-admin.card class="max-w-3xl">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="label" label="Label (field name)" :value="$field->label" required maxlength="150" placeholder="e.g. Location (city / area)" class="sm:col-span-2" />

                @if ($field->is_system)
                    <div>
                        <span class="a-label">Type</span>
                        <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $field->typeLabel() }} <span class="text-xs text-slate-400">(fixed)</span></p>
                    </div>
                @else
                    <div>
                        <x-admin.select name="type" label="Type of box" :value="$field->type" :options="\App\Models\LeadField::TYPES" required x-model="type" />
                    </div>
                @endif

                <x-admin.input name="placeholder" label="Example text inside the box" :value="$field->placeholder" maxlength="150" placeholder="e.g. Faisalabad, Jhumra Road" />

                @unless ($field->is_system)
                    <div x-show="type === 'select'" x-collapse class="sm:col-span-2">
                        <x-admin.textarea name="options" label="Choices (one per line)" :value="implode(PHP_EOL, $field->options ?? [])" rows="5" help="Shown as a dropdown, e.g. WhatsApp, Phone call, Facebook comment." />
                    </div>
                @endunless

                <x-admin.input name="help" label="Hint under the box (optional)" :value="$field->help" maxlength="255" class="sm:col-span-2" />

                <div class="grid gap-4 sm:col-span-2 sm:grid-cols-3">
                    @if ($field->isLocked())
                        <p class="text-sm text-slate-500 sm:col-span-3">This field is always on the form and always required.</p>
                        <input type="hidden" name="is_active" value="1"><input type="hidden" name="is_required" value="1"><input type="hidden" name="show_in_list" value="0">
                    @else
                        <x-admin.toggle name="is_active" label="Show on the form" :checked="$field->is_active" />
                        <x-admin.toggle name="is_required" label="Required" :checked="$field->is_required" help="The lead cannot be saved without it." />
                        @if ($field->is_system)
                            <input type="hidden" name="show_in_list" value="0">
                        @else
                            <x-admin.toggle name="show_in_list" label="Show as a column in the leads list" :checked="$field->show_in_list" />
                        @endif
                    @endif
                </div>
            </div>
        </x-admin.card>

        <x-admin.form-actions :cancel="route('admin.lead-fields.index')" :label="$field->exists ? 'Save Field' : 'Add Field'" />
    </form>
@endsection
