@extends('layouts.admin')

@section('title', $testimonial->exists ? 'Edit Testimonial' : 'Add Testimonial')

@section('breadcrumbs')
    <a href="{{ route('admin.testimonials.index') }}" class="hover:text-slate-900">Happy Clients</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $testimonial->client_name ?? 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" enctype="multipart/form-data">
        @csrf @if ($testimonial->exists) @method('PUT') @endif
        <x-admin.page-header :title="$testimonial->exists ? 'Review from '.$testimonial->client_name : 'Add Testimonial'" />

        <div class="grid gap-6 xl:grid-cols-3">
            <x-admin.card title="Review" class="xl:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="client_name" label="Client name" :value="$testimonial->client_name" required />
                    <x-admin.input name="client_title" label="Title under name" :value="$testimonial->client_title" placeholder="Happy Client" />
                    <x-admin.input name="location" label="Client's city / location" :value="$testimonial->location" />
                    <x-admin.select name="plot_id" label="Plot size bought" :value="$testimonial->plot_id" :options="$plots" placeholder="Not linked" />
                    <x-admin.textarea name="review" label="Review" :value="$testimonial->review" rows="5" required class="sm:col-span-2" />
                    <div x-data="{ rating: {{ (int) old('rating', $testimonial->rating) }} }">
                        <span class="a-label">Rating</span>
                        <input type="hidden" name="rating" :value="rating">
                        <div class="flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" class="text-3xl leading-none transition" :class="rating >= {{ $i }} ? 'text-gold-500' : 'text-slate-300 hover:text-gold-300'" aria-label="{{ $i }} stars">★</button>
                            @endfor
                        </div>
                    </div>
                    <x-admin.input name="video_url" type="url" label="Video review link (YouTube / Vimeo)" :value="$testimonial->video_url" />
                </div>
            </x-admin.card>

            <div class="space-y-6">
                <x-admin.card title="Photo">
                    <x-admin.image name="client_image" :current="$testimonial->client_image" aspect="aspect-square" help="Optional. Initials are shown when empty." />
                </x-admin.card>
                <x-admin.card title="Publishing">
                    <div class="space-y-4">
                        <x-admin.toggle name="is_active" label="Show on website" :checked="$testimonial->is_active" />
                        <x-admin.toggle name="is_featured" label="Featured (shown first)" :checked="$testimonial->is_featured" />
                        <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$testimonial->sort_order" />
                    </div>
                </x-admin.card>
            </div>
        </div>
        <x-admin.form-actions :cancel="route('admin.testimonials.index')" />
    </form>
@endsection
