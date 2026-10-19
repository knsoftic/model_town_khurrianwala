@extends('layouts.admin')

@php
    $editing = $section->exists;
    $fields = \App\Support\SectionFields::for($section->type);
    $sources = [
        'intro' => ['Statistics', 'admin.statistics.index'],
        'plots' => ['Plot Sizes', 'admin.plots.index'],
        'installment_plans' => ['Installment Plans', 'admin.installment-plans.index'],
        'calculator' => ['Installment Plans', 'admin.installment-plans.index'],
        'features' => ['Project Features', 'admin.features.index'],
        'location' => ['Locations', 'admin.locations.index'],
        'development' => ['Development Progress', 'admin.development.index'],
        'testimonials' => ['Testimonials', 'admin.testimonials.index'],
        'gallery' => ['Gallery', 'admin.gallery.index'],
        'video' => ['Videos', 'admin.videos.index'],
        'faq' => ['FAQs', 'admin.faqs.index'],
    ];
@endphp

@section('title', $editing ? 'Edit '.$section->name : 'Add Section')

@section('breadcrumbs')
    <a href="{{ route('admin.sections.index') }}" class="hover:text-slate-900">Homepage Sections</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $section->name }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.sections.update', $section) : route('admin.sections.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($editing) @method('PUT') @else <input type="hidden" name="type" value="{{ $section->type }}"> @endif

        <x-admin.page-header :title="$editing ? $section->name : 'Add '.$section->typeLabel()" :description="$section->typeLabel().' section'">
            <x-slot:actions>
                <a href="{{ route('home') }}" target="_blank" class="a-btn-secondary"><x-icon name="external-link" class="size-4" /> View homepage</a>
                <button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save</button>
            </x-slot:actions>
        </x-admin.page-header>

        @if (isset($sources[$section->type]))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                <x-icon name="info" class="size-5 text-sky-600" />
                <span>The items in this section come from <a href="{{ route($sources[$section->type][1]) }}" class="font-semibold underline">{{ $sources[$section->type][0] }}</a>. Here you edit the section's headings, buttons and layout.</span>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <x-admin.card title="Text">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="eyebrow" label="Small label above heading" :value="$section->eyebrow" />
                        <x-admin.input name="heading" label="Heading" :value="$section->heading" />
                        <x-admin.textarea name="subheading" label="Subheading" :value="$section->subheading" rows="2" class="sm:col-span-2" />
                        <x-admin.textarea name="description" label="Description" :value="$section->description" rows="4" class="sm:col-span-2" />
                    </div>
                </x-admin.card>

                @if ($fields)
                    <x-admin.card title="Section settings">
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($fields as $key => $field)
                                <x-admin.field :name="'settings['.$key.']'" :field="$field" :value="$section->settings[$key] ?? null" :file-name="'media['.$key.']'" />
                            @endforeach
                        </div>
                    </x-admin.card>
                @endif
            </div>

            <div class="space-y-6">
                <x-admin.card title="Section">
                    <div class="space-y-4">
                        <x-admin.input name="name" label="Name (admin only)" :value="$section->name" required />
                        <x-admin.toggle name="is_active" label="Show on homepage" :checked="$section->is_active" />
                    </div>
                </x-admin.card>
                @if ($editing)
                    <x-admin.card title="Danger zone">
                        <button type="button" class="a-btn-ghost a-btn-sm text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.sections.destroy', $section)), title: 'Remove this section?', message: 'You can add it again later from “Add section”.', button: 'Remove' })"><x-icon name="trash" class="size-3.5" /> Remove section</button>
                    </x-admin.card>
                @endif
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.sections.index')" />
    </form>
@endsection
