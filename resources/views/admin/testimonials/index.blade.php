@extends('layouts.admin')

@section('title', 'Testimonials')

@section('content')
    <x-admin.page-header title="Happy Clients" description="Client reviews shown on the homepage slider, the Testimonials page and plot pages. Drag to reorder.">
        <x-slot:actions><a href="{{ route('admin.testimonials.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add Testimonial</a></x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" x-data="sortable(@js(route('admin.reorder', 'testimonials')))">
        @forelse ($testimonials as $testimonial)
            <div data-id="{{ $testimonial->id }}" class="a-card flex flex-col p-5">
                <div class="flex items-start gap-3">
                    <span data-drag class="cursor-grab text-slate-400 hover:text-slate-700"><x-icon name="grip-vertical" class="size-4" /></span>
                    @if ($testimonial->client_image)
                        <img src="{{ media_url($testimonial->client_image) }}" alt="" class="size-11 rounded-full object-cover">
                    @else
                        <span class="grid size-11 place-items-center rounded-full bg-navy-900 text-sm font-semibold text-gold-400">{{ $testimonial->initials() }}</span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-slate-900">{{ $testimonial->client_name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ collect([$testimonial->client_title, $testimonial->location, $testimonial->plot?->size_label])->filter()->implode(' · ') }}</p>
                        <p class="mt-0.5 text-sm text-gold-500">{{ str_repeat('★', $testimonial->rating) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $testimonial->rating) }}</span></p>
                    </div>
                </div>
                <p class="mt-3 line-clamp-3 flex-1 text-sm text-slate-600">“{{ $testimonial->review }}”</p>
                @if ($testimonial->video_url)<p class="mt-2 flex items-center gap-1 text-xs text-sky-700"><x-icon name="circle-play" class="size-3.5" /> Video review</p>@endif
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">Visible <x-admin.switch :url="route('admin.toggle', ['testimonials', $testimonial->id])" :value="$testimonial->is_active" /></label>
                        <label class="flex items-center gap-2">Featured <x-admin.switch :url="route('admin.toggle', ['testimonials', $testimonial->id])" :value="$testimonial->is_featured" field="is_featured" /></label>
                    </div>
                    <x-admin.row-actions :edit="route('admin.testimonials.edit', $testimonial)" :delete="route('admin.testimonials.destroy', $testimonial)" :delete-label="'review from '.$testimonial->client_name" />
                </div>
            </div>
        @empty
            <x-admin.card class="md:col-span-2 xl:col-span-3"><x-admin.empty icon="quote" title="No testimonials yet" /></x-admin.card>
        @endforelse
    </div>
@endsection
