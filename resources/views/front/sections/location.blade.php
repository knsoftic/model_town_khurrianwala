@if (! empty($location))
    @php($map = $location->mapEmbedSrc())
    <section id="location" class="relative overflow-hidden bg-primary py-20 text-white sm:py-28">
        <div class="pointer-events-none absolute inset-0 bg-grid-light"></div>
        <div class="relative container-x grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div x-reveal>
                <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" align="left" :dark="true" />

                <div class="mt-8 flex items-start gap-4 rounded-brand-lg border border-white/10 bg-white/5 p-5">
                    <span class="grid size-12 shrink-0 place-items-center rounded-full bg-accent text-primary"><x-icon name="map-pin" class="size-5" /></span>
                    <div>
                        <h3 class="font-sans text-lg font-semibold">{{ $location->name }}</h3>
                        <p class="mt-1 text-sm text-white/70">{{ $location->fullAddress() }}</p>
                    </div>
                </div>

                @if ($location->distances())
                    <dl class="mt-5 grid gap-3 sm:grid-cols-3">
                        @foreach ($location->distances() as $distance)
                            <div class="rounded-brand border border-white/10 bg-white/5 p-4">
                                <x-icon :name="$distance['icon']" class="size-5 text-accent" />
                                <dt class="mt-3 text-xs text-white/60">{{ $distance['label'] }}</dt>
                                <dd class="mt-0.5 text-sm font-semibold">{{ $distance['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($location->landmarks)
                    <ul class="mt-6 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                        @foreach ($location->landmarks as $landmark)
                            <li class="flex items-center justify-between gap-3 border-b border-white/10 pb-3 text-sm">
                                <span class="flex items-center gap-2 text-white/85"><x-icon name="navigation" class="size-3.5 text-accent" /> {{ $landmark['name'] ?? '' }}</span>
                                <span class="font-semibold text-accent">{{ $landmark['distance'] ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('locations.show', $location) }}" class="btn-primary">Location Details <x-icon name="arrow-right" class="size-4" /></a>
                    @if ($location->directionsUrl())
                        <a href="{{ $location->directionsUrl() }}" target="_blank" rel="noopener" class="btn-ghost-light"><x-icon name="navigation" class="size-4" /> Get Directions</a>
                    @endif
                </div>
            </div>

            <div class="relative" x-reveal>
                <div class="absolute -inset-3 rounded-[calc(var(--brand-radius)*2)] border border-accent/30"></div>
                <div class="relative overflow-hidden rounded-brand-lg bg-white/5 shadow-2xl ring-1 ring-white/10">
                    @if ($map)
                        <iframe src="{{ $map }}" title="Map — {{ $location->name }}" class="aspect-[4/3] w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    @elseif ($location->image)
                        <img {{ img_attrs($location->image, '(min-width: 1024px) 50vw, 100vw', [480, 768, 960, 1280]) }} alt="{{ $location->name }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
