@php
    $withPlans = ($plots ?? collect())->filter(fn ($plot) => $plot->activePlans->isNotEmpty())->values();
@endphp

@if ($withPlans->isNotEmpty())
    <section id="payment-schedule" class="relative py-20 sm:py-28" x-data="{ tab: 0 }">
        <div class="container-x">
            <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" />

            <div class="mt-10 flex justify-center">
                <div class="scrollbar-none -mx-4 flex max-w-full gap-2 overflow-x-auto px-4 pb-1" role="tablist">
                    @foreach ($withPlans as $i => $plot)
                        <button type="button" role="tab" @click="tab = {{ $i }}" :aria-selected="tab === {{ $i }}"
                                :class="tab === {{ $i }} ? 'bg-primary text-white shadow-lg' : 'bg-white text-primary ring-1 ring-primary/10 hover:ring-accent'"
                                class="shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold transition">
                            {{ $plot->size_label }}
                        </button>
                    @endforeach
                </div>
            </div>

            @foreach ($withPlans as $i => $plot)
                @php($plan = $plot->activePlans->first())
                @php($summary = $plan->summary())
                <div x-show="tab === {{ $i }}" @if ($i > 0) x-cloak @endif x-transition.opacity.duration.300ms role="tabpanel"
                     class="mt-10 grid gap-8 lg:grid-cols-[1fr_380px] lg:gap-10">
                    <x-front.payment-timeline :plan="$plan" />

                    <aside class="lg:sticky lg:top-28 lg:self-start">
                        <div class="card overflow-hidden">
                            <div class="relative aspect-[16/9] bg-surface">
                                @if ($plot->image)
                                    <img {{ img_attrs($plot->image, '(min-width: 1024px) 380px, 100vw', [320, 480, 640, 960]) }} alt="{{ $plot->title }}" loading="lazy" class="size-full object-cover">
                                @endif
                                <x-front.status-badge :status="$plot->status" class="absolute top-3 left-3 bg-white!" />
                            </div>
                            <div class="p-6">
                                <p class="text-xs font-semibold tracking-wider text-accent-ink uppercase">{{ $plot->size_label }}</p>
                                <h3 class="mt-1 text-xl font-semibold text-primary">{{ $plan->name }}</h3>
                                @if ($plan->description)
                                    <p class="mt-2 text-sm text-ink/70">{{ $plan->description }}</p>
                                @endif
                                <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-brand-sm bg-surface p-3"><dt class="text-xs text-ink/70">Total Price</dt><dd class="mt-0.5 font-semibold text-primary">{{ money($summary->totalPrice) }}</dd></div>
                                    <div class="rounded-brand-sm bg-surface p-3"><dt class="text-xs text-ink/70">Booking</dt><dd class="mt-0.5 font-semibold text-primary">{{ money($summary->amountFor('booking')) }}</dd></div>
                                    <div class="rounded-brand-sm bg-surface p-3"><dt class="text-xs text-ink/70">Installment</dt><dd class="mt-0.5 font-semibold text-primary">{{ money($summary->installmentAmount) }}</dd></div>
                                    <div class="rounded-brand-sm bg-surface p-3"><dt class="text-xs text-ink/70">Duration</dt><dd class="mt-0.5 font-semibold text-primary">{{ $summary->durationMonths }} months</dd></div>
                                </dl>
                                <div class="mt-6 grid gap-2.5">
                                    @if ($plot->isBookable())
                                        <a href="{{ route('book-plot', ['plot' => $plot->slug, 'plan' => $plan->slug]) }}" class="btn-primary">Book This Plan</a>
                                    @endif
                                    <a href="{{ route('plots.show', $plot) }}#payment-plan" class="btn-outline">View {{ $plot->size_label }} Details</a>
                                </div>
                                @if ($plot->activePlans->count() > 1)
                                    <p class="mt-4 text-center text-xs text-ink/70">+{{ $plot->activePlans->count() - 1 }} more {{ str('plan')->plural($plot->activePlans->count() - 1) }} available for {{ $plot->size_label }}</p>
                                @endif
                            </div>
                        </div>
                    </aside>
                </div>
            @endforeach

            @if ($section->setting('button_text'))
                <div class="mt-12 text-center">
                    <a href="{{ url($section->setting('button_url', '/installment-plans')) }}" class="btn-dark !px-7">{{ $section->setting('button_text') }} <x-icon name="arrow-right" class="size-4" /></a>
                </div>
            @endif
        </div>
    </section>
@endif
