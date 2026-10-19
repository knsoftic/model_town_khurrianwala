@extends('layouts.front')

@php
    $images = $albums->flatMap(fn ($album) => $album->activeImages->each->setRelation('album', $album));
@endphp

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['Gallery' => null]" />

    <section class="py-16 sm:py-24">
        <div class="container-x">
            @if ($page->content)
                <div class="prose-brand mx-auto mb-12 max-w-3xl text-center">{!! $page->content !!}</div>
            @endif

            @if ($images->isEmpty())
                <p class="rounded-brand-lg bg-white p-12 text-center text-ink/60">Photos will be added soon.</p>
            @else
                <x-front.gallery-grid :images="$images" :albums="$albums" />
            @endif
        </div>
    </section>

    @if ($videos->isNotEmpty())
        <section class="bg-white py-16 sm:py-24">
            <div class="container-x">
                <x-front.section-heading eyebrow="Videos" heading="Discover Our Project" />
                <div @class(['mt-12 grid gap-6', 'md:grid-cols-2' => $videos->count() > 1])>
                    @foreach ($videos as $video)
                        <x-front.video-player :video="$video" :class="$videos->count() === 1 ? 'mx-auto w-full max-w-5xl' : 'w-full'" />
                    @endforeach
                </div>
            </div>
        </section>
        @include('front.partials.video-modal')
    @endif
@endsection
