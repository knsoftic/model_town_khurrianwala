@extends('layouts.front')

@php
    $plans = $plot->activePlans;
    $gallery = collect([$plot->image])->filter()->merge($plot->images->pluck('path'))->values();
    $whatsapp = whatsapp_url(str_replace('{plot}', $plot->size_label, (string) setting('whatsapp_plot_message')));
    $facts = array_filter([
        ['Total Price', money($plot->total_price), 'banknote'],
        ['Lump-sum / Starting From', money($plot->starting_price), 'badge-percent'],
        ['Booking Amount', money($plot->booking_amount), 'bookmark-check'],
        ['Down / Advance Payment', money($plot->down_payment), 'wallet'],
        ['Installment', $plot->installment_amount ? money($plot->installment_amount).' '.$plot->frequencySuffix() : null, 'calendar-days'],
        ['No. of Installments', $plot->installment_count ? $plot->installment_count.' ('.$plot->frequencyLabel().')' : null, 'list-ordered'],
        ['After 6 Months', money($plot->after_6_months_payment), 'calendar-clock'],
        ['After 12 Months', money($plot->after_12_months_payment), 'calendar-clock'],
        ['End Payment', money($plot->end_payment), 'key-round'],
        ['Development Charges', money($plot->development_charges), 'hard-hat'],
        ['Possession Charges', money($plot->possession_charges), 'house'],
        ['Dimensions', $plot->dimensions ? $plot->dimensions.' ft' : null, 'ruler'],
        ['Covered Area', $plot->area_sqft ? number_format($plot->area_sqft).' sq ft' : null, 'land-plot'],
        ['Availability', $plot->total_units ? $plot->availableUnits().' of '.$plot->total_units.' plots' : null, 'grid-3x3'],
    ], fn ($fact) => $fact[1] !== null && $fact[1] !== '—');
@endphp

@section('content')
    <x-front.page-banner :eyebrow="$plot->size_label.' · '.(\App\Models\Plot::CATEGORIES[$plot->category] ?? $plot->category)" :heading="$plot->title" :subheading="$plot->short_description"
                         :image="$banner" :crumbs="['Plot Sizes' => route('plots.index'), $plot->size_label => null]">
        <div class="mt-8 flex flex-wrap items-center gap-3">
            <x-front.status-badge :status="$plot->status" class="bg-white! !py-1.5" />
            @if ($plot->is_demo_price)
                <span class="rounded-full bg-white/10 px-3 py-1.5 text-[11px] font-semibold tracking-wide uppercase">{{ $plot->price_note ?: 'Demo Price' }}</span>
            @endif
            @if ($plot->location)
                <a href="{{ route('locations.show', $plot->location) }}" class="inline-flex items-center gap-1.5 text-sm text-white/75 hover:text-accent"><x-icon name="map-pin" class="size-4" /> {{ $plot->location->name }}</a>
            @endif
        </div>
        <dl class="mt-8 grid max-w-2xl grid-cols-3 gap-3">
            <div class="rounded-brand bg-white/10 p-4 backdrop-blur"><dt class="text-[11px] tracking-wider text-white/60 uppercase">Starting From</dt><dd class="mt-1 font-heading text-lg font-semibold text-accent sm:text-xl">{{ money($plot->displayPrice()) }}</dd></div>
            <div class="rounded-brand bg-white/10 p-4 backdrop-blur"><dt class="text-[11px] tracking-wider text-white/60 uppercase">Booking</dt><dd class="mt-1 font-heading text-lg font-semibold sm:text-xl">{{ money($plot->booking_amount) }}</dd></div>
            <div class="rounded-brand bg-white/10 p-4 backdrop-blur"><dt class="text-[11px] tracking-wider text-white/60 uppercase">Installment</dt><dd class="mt-1 font-heading text-lg font-semibold sm:text-xl">{{ money($plot->installment_amount) }}</dd></div>
        </dl>
        <div class="mt-8 flex flex-wrap gap-3">
            @if ($plot->isBookable())
                <a href="{{ route('book-plot', ['plot' => $plot->slug, 'plan' => $plans->first()?->slug]) }}" class="btn-primary !px-6 !py-3.5"><x-icon name="key-round" class="size-4" /> Book This Plot</a>
            @endif
            <a href="#payment-plan" class="btn-ghost-light !px-6 !py-3.5">View Payment Plan</a>
            @if ($whatsapp)
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-whatsapp !px-6 !py-3.5"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
            @endif
        </div>
    </x-front.page-banner>

    <div class="container-x grid gap-10 py-14 lg:grid-cols-[minmax(0,1fr)_380px] lg:py-20">
        <div class="min-w-0 space-y-14">
            {{-- Gallery --}}
            @if ($gallery->isNotEmpty())
                <div x-data="{ active: 0 }">
                    <div class="overflow-hidden rounded-brand-lg bg-white shadow-soft">
                        @foreach ($gallery as $i => $path)
                            <img x-show="active === {{ $i }}" @if ($i) x-cloak @endif src="{{ media_url($path) }}" alt="{{ $plot->title }} — image {{ $i + 1 }}" class="aspect-[16/10] w-full object-cover" @if ($i) loading="lazy" @endif>
                        @endforeach
                    </div>
                    @if ($gallery->count() > 1)
                        <div class="scrollbar-none mt-3 flex gap-3 overflow-x-auto">
                            @foreach ($gallery as $i => $path)
                                <button type="button" @click="active = {{ $i }}" :class="active === {{ $i }} ? 'ring-2 ring-accent' : 'opacity-70 hover:opacity-100'" class="shrink-0 overflow-hidden rounded-brand-sm transition">
                                    <img src="{{ media_url($path) }}" alt="" loading="lazy" class="h-16 w-24 object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Key figures --}}
            <section>
                <h2 class="text-2xl font-semibold text-primary sm:text-3xl">Price &amp; Payment Details</h2>
                <dl class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($facts as [$label, $value, $icon])
                        <div class="flex items-center gap-3.5 rounded-brand border border-primary/8 bg-white p-4">
                            <span class="grid size-10 shrink-0 place-items-center rounded-brand-sm bg-accent/12 text-accent"><x-icon :name="$icon" class="size-5" /></span>
                            <div class="min-w-0">
                                <dt class="text-xs text-ink/55">{{ $label }}</dt>
                                <dd class="truncate font-semibold text-primary">{{ $value }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
                @if ($plot->price_note)
                    <p class="mt-3 text-xs text-ink/50">{{ $plot->price_note }}</p>
                @endif
            </section>

            @if ($plot->full_description)
                <section>
                    <h2 class="text-2xl font-semibold text-primary sm:text-3xl">About This Plot</h2>
                    <div class="prose-brand mt-5">{!! $plot->full_description !!}</div>
                </section>
            @endif

            {{-- Installment plans --}}
            @if ($plans->isNotEmpty())
                <section id="payment-plan" class="scroll-mt-28" x-data="{ plan: 0 }">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <span class="eyebrow">Payment Schedule</span>
                            <h2 class="mt-2 text-2xl font-semibold text-primary sm:text-3xl">Installment Plan</h2>
                        </div>
                        @if ($plans->count() > 1)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($plans as $i => $plan)
                                    <button type="button" @click="plan = {{ $i }}" :class="plan === {{ $i }} ? 'bg-primary text-white' : 'bg-white text-primary ring-1 ring-primary/10'" class="rounded-full px-4 py-2 text-xs font-semibold transition">{{ $plan->name }}</button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @foreach ($plans as $i => $plan)
                        <div x-show="plan === {{ $i }}" @if ($i) x-cloak @endif class="mt-6">
                            @if ($plan->description)
                                <p class="mb-5 text-ink/70">{{ $plan->description }}</p>
                            @endif
                            <x-front.payment-timeline :plan="$plan" />
                            @if ($plan->terms)
                                <p class="mt-4 flex gap-2 text-xs text-ink/55"><x-icon name="info" class="size-4 text-accent" /> {{ $plan->terms }}</p>
                            @endif
                            @if ($plot->isBookable())
                                <a href="{{ route('book-plot', ['plot' => $plot->slug, 'plan' => $plan->slug]) }}" class="btn-primary mt-6 !px-7">Book with {{ $plan->name }} <x-icon name="arrow-right" class="size-4" /></a>
                            @endif
                        </div>
                    @endforeach
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-primary sm:text-3xl">Payment Calculator</h2>
                    <x-front.calculator class="mt-6 !shadow-soft ring-1 ring-primary/8" :initial="$plot->slug" />
                </section>
            @endif

            {{-- Location --}}
            @if ($plot->location)
                @php($map = $plot->location->mapEmbedSrc())
                <section class="overflow-hidden rounded-brand-lg border border-primary/8 bg-white shadow-soft">
                    <div class="grid md:grid-cols-2">
                        <div class="p-6 sm:p-8">
                            <span class="eyebrow">Location</span>
                            <h2 class="mt-2 text-2xl font-semibold text-primary">{{ $plot->location->name }}</h2>
                            <p class="mt-2 text-sm text-ink/60">{{ $plot->location->fullAddress() }}</p>
                            <ul class="mt-5 space-y-2.5 text-sm">
                                @foreach ($plot->location->distances() as $distance)
                                    <li class="flex items-center justify-between gap-3 border-b border-primary/6 pb-2.5"><span class="flex items-center gap-2 text-ink/70"><x-icon :name="$distance['icon']" class="size-4 text-accent" /> {{ $distance['label'] }}</span><span class="font-semibold text-primary">{{ $distance['value'] }}</span></li>
                                @endforeach
                            </ul>
                            <a href="{{ route('locations.show', $plot->location) }}" class="btn-outline mt-6 !py-2.5">Location Details</a>
                        </div>
                        @if ($map)
                            <iframe src="{{ $map }}" title="Map" class="min-h-72 w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        @endif
                    </div>
                </section>
            @endif

            @if ($features->isNotEmpty())
                <section>
                    <h2 class="text-2xl font-semibold text-primary sm:text-3xl">Community Features</h2>
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($features as $feature)
                            <div class="flex flex-col items-center gap-2 rounded-brand border border-primary/8 bg-white p-4 text-center">
                                <x-icon :name="$feature->icon ?: 'circle-check'" class="size-7 text-accent" />
                                <span class="text-sm font-medium text-primary">{{ $feature->title }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($testimonials->isNotEmpty())
                <section>
                    <h2 class="text-2xl font-semibold text-primary sm:text-3xl">What {{ $plot->size_label }} Owners Say</h2>
                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        @foreach ($testimonials as $testimonial)
                            <x-front.testimonial-card :testimonial="$testimonial" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($faqs->isNotEmpty())
                <section>
                    <h2 class="text-2xl font-semibold text-primary sm:text-3xl">Frequently Asked Questions</h2>
                    <x-front.faq-list :faqs="$faqs" class="mt-6" />
                </section>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="card overflow-hidden">
                <div class="bg-primary p-6 text-white">
                    <p class="text-xs font-semibold tracking-wider text-accent uppercase">{{ $plot->size_label }}</p>
                    <p class="mt-1 font-heading text-3xl font-semibold">{{ money($plot->displayPrice()) }}</p>
                    <p class="mt-1 text-sm text-white/65">Booking {{ money($plot->booking_amount) }} · {{ money($plot->installment_amount) }} {{ $plot->frequencySuffix() }}</p>
                </div>
                <div class="p-6">
                    <x-front.lead-form :plots="collect([$plot])" :selected="$plot->id" source="plot_page" :compact="true"
                                       :title="setting('lead_form_title')" submit-label="Get Payment Plan" />
                    <div class="mt-5 grid grid-cols-2 gap-2.5 border-t border-primary/8 pt-5">
                        @if (setting('phone'))
                            <a href="{{ tel_url(setting('phone')) }}" class="btn-outline !px-3 !py-2.5 text-xs"><x-icon name="phone" class="size-4" /> Call Now</a>
                        @endif
                        @if ($whatsapp)
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-whatsapp !px-3 !py-2.5 text-xs"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                        @endif
                    </div>
                </div>
            </div>
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="bg-white py-16 sm:py-20">
            <div class="container-x">
                <x-front.section-heading eyebrow="More Options" heading="Other Plot Sizes" />
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-front.plot-card :plot="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
