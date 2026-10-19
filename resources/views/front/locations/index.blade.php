@extends('layouts.front')

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['Locations' => null]" />

    <section class="py-16 sm:py-24">
        <div class="container-x space-y-10">
            @if ($page->content)
                <div class="prose-brand mx-auto max-w-3xl text-center">{!! $page->content !!}</div>
            @endif

            @forelse ($locations as $location)
                @php($map = $location->mapEmbedSrc())
                <article class="grid overflow-hidden rounded-brand-lg border border-primary/8 bg-white shadow-soft lg:grid-cols-2">
                    <div class="p-6 sm:p-10">
                        @if ($location->is_featured)
                            <span class="eyebrow">Prime Location</span>
                        @endif
                        <h2 class="mt-2 text-3xl font-semibold text-primary">{{ $location->name }}</h2>
                        <p class="mt-2 flex items-start gap-2 text-ink/70"><x-icon name="map-pin" class="mt-0.5 size-4 text-accent" /> {{ $location->fullAddress() }}</p>
                        @if ($location->short_description)
                            <p class="mt-4 text-ink/70">{{ $location->short_description }}</p>
                        @endif

                        @if ($location->distances())
                            <dl class="mt-6 grid grid-cols-3 gap-3">
                                @foreach ($location->distances() as $distance)
                                    <div class="rounded-brand bg-surface p-3.5">
                                        <x-icon :name="$distance['icon']" class="size-5 text-accent" />
                                        <dt class="mt-2 text-xs text-ink/70">{{ $distance['label'] }}</dt>
                                        <dd class="text-sm font-semibold text-primary">{{ $distance['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('locations.show', $location) }}" class="btn-dark">View Location <x-icon name="arrow-right" class="size-4" /></a>
                            @if ($location->directionsUrl())
                                <a href="{{ $location->directionsUrl() }}" target="_blank" rel="noopener" class="btn-outline"><x-icon name="navigation" class="size-4" /> Directions</a>
                            @endif
                        </div>
                        @if ($location->plots_count)
                            <p class="mt-6 text-sm text-ink/70">{{ $location->plots_count }} plot {{ str('size')->plural($location->plots_count) }} available at this location.</p>
                        @endif
                    </div>
                    <div class="min-h-80 bg-surface">
                        @if ($map)
                            <iframe src="{{ $map }}" title="Map — {{ $location->name }}" class="size-full min-h-80" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        @elseif ($location->image)
                            <img {{ img_attrs($location->image, '(min-width: 1024px) 50vw, 100vw', [480, 768, 960, 1280]) }} alt="{{ $location->name }}" class="size-full object-cover" loading="lazy">
                        @endif
                    </div>
                </article>
            @empty
                <p class="rounded-brand-lg bg-white p-12 text-center text-ink/70">Location details will be published soon.</p>
            @endforelse
        </div>
    </section>

    @include('front.partials.cta-band')
@endsection
