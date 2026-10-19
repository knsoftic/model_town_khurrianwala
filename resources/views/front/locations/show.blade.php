@extends('layouts.front')

@php($map = $location->mapEmbedSrc())

@section('content')
    <x-front.page-banner eyebrow="Prime Location" :heading="$location->name" :subheading="$location->short_description" :image="$location->image"
                         :crumbs="['Locations' => route('locations.index'), $location->name => null]">
        <p class="mt-6 flex items-start gap-2 text-white/75"><x-icon name="map-pin" class="mt-0.5 size-4 text-accent" /> {{ $location->fullAddress() }}</p>
    </x-front.page-banner>

    <section class="py-14 sm:py-20">
        <div class="container-x grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-10">
                @if ($map)
                    <div class="overflow-hidden rounded-brand-lg shadow-lift ring-1 ring-primary/8">
                        <iframe src="{{ $map }}" title="Map — {{ $location->name }}" class="aspect-[16/10] w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                @endif

                @if ($location->description)
                    <div class="prose-brand">{!! $location->description !!}</div>
                @endif

                @if ($location->plots->isNotEmpty())
                    <div>
                        <h2 class="text-2xl font-semibold text-primary">Plots at This Location</h2>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            @foreach ($location->plots as $plot)
                                <x-front.plot-card :plot="$plot" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                @if ($location->distances())
                    <div class="card p-6">
                        <h2 class="font-sans text-lg font-semibold text-primary">Distances</h2>
                        <dl class="mt-4 space-y-3">
                            @foreach ($location->distances() as $distance)
                                <div class="flex items-center justify-between gap-4 rounded-brand bg-surface px-4 py-3">
                                    <dt class="flex items-center gap-2.5 text-sm text-ink/70"><x-icon :name="$distance['icon']" class="size-5 text-accent" /> {{ $distance['label'] }}</dt>
                                    <dd class="font-semibold text-primary">{{ $distance['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                @if ($location->landmarks)
                    <div class="card p-6">
                        <h2 class="font-sans text-lg font-semibold text-primary">Nearby Landmarks</h2>
                        <ul class="mt-4 divide-y divide-primary/6">
                            @foreach ($location->landmarks as $landmark)
                                <li class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <span class="flex items-center gap-2 text-ink/75"><x-icon name="navigation" class="size-4 text-accent" /> {{ $landmark['name'] ?? '' }}</span>
                                    <span class="font-semibold text-primary">{{ $landmark['distance'] ?? '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-brand-lg bg-primary p-6 text-white">
                    <h2 class="font-sans text-lg font-semibold">Plan a Site Visit</h2>
                    <p class="mt-1.5 text-sm text-white/70">See the project and available plots in person with our team.</p>
                    <div class="mt-5 grid gap-2.5">
                        @if ($location->directionsUrl())
                            <a href="{{ $location->directionsUrl() }}" target="_blank" rel="noopener" class="btn-primary"><x-icon name="navigation" class="size-4" /> Get Directions</a>
                        @endif
                        <button type="button" @click="$store.callback.show()" class="btn-ghost-light"><x-icon name="phone-call" class="size-4" /> Schedule a Visit</button>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if ($features->isNotEmpty())
        <section class="bg-white py-16 sm:py-20">
            <div class="container-x">
                <x-front.section-heading eyebrow="Amenities" heading="Community Features" />
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($features as $feature)
                        <x-front.feature-card :feature="$feature" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
