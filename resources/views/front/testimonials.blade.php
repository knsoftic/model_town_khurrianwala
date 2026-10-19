@extends('layouts.front')

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['Testimonials' => null]">
        @if ($testimonials->isNotEmpty())
            <div class="mt-8 flex items-center gap-4">
                <div class="flex -space-x-3">
                    @foreach ($testimonials->take(4) as $testimonial)
                        <span class="grid size-11 place-items-center rounded-full bg-accent font-heading text-sm font-semibold text-primary ring-2 ring-primary">{{ $testimonial->initials() }}</span>
                    @endforeach
                </div>
                <div>
                    <p class="font-semibold">{{ number_format($testimonials->avg('rating'), 1) }} / 5 average rating</p>
                    <p class="text-sm text-white/65">From {{ $testimonials->count() }} client {{ str('review')->plural($testimonials->count()) }}</p>
                </div>
            </div>
        @endif
    </x-front.page-banner>

    <section class="py-16 sm:py-24">
        <div class="container-x">
            @if ($testimonials->isEmpty())
                <p class="rounded-brand-lg bg-white p-12 text-center text-ink/70">Client reviews will be published soon.</p>
            @else
                <div class="columns-1 gap-6 md:columns-2 xl:columns-3 [&>*]:mb-6">
                    @foreach ($testimonials as $testimonial)
                        <x-front.testimonial-card :testimonial="$testimonial" class="break-inside-avoid" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @include('front.partials.video-modal')
    @include('front.partials.cta-band')
@endsection
