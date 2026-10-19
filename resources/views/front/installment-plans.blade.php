@extends('layouts.front')

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['Installment Plans' => null]" />

    @if ($plots->isEmpty())
        <section class="container-x py-24 text-center text-ink/70">Installment plans will be published soon.</section>
    @else
        <section class="py-16 sm:py-24" x-data="{ plot: @js($selected?->slug), plan: 0 }">
            <div class="container-x">
                @if ($page->content)
                    <div class="prose-brand mx-auto mb-12 max-w-3xl text-center">{!! $page->content !!}</div>
                @endif

                {{-- Plot size tabs --}}
                <div class="flex justify-center">
                    <div class="scrollbar-none -mx-4 flex max-w-full gap-2 overflow-x-auto px-4 pb-1" role="tablist">
                        @foreach ($plots as $plot)
                            <button type="button" role="tab" @click="plot = @js($plot->slug); plan = 0" :aria-selected="plot === @js($plot->slug)"
                                    :class="plot === @js($plot->slug) ? 'bg-primary text-white shadow-lg' : 'bg-white text-primary ring-1 ring-primary/10 hover:ring-accent'"
                                    class="shrink-0 rounded-full px-6 py-3 text-sm font-semibold transition">{{ $plot->size_label }}</button>
                        @endforeach
                    </div>
                </div>

                @foreach ($plots as $plot)
                    <div x-show="plot === @js($plot->slug)" @if (! $selected || $selected->isNot($plot)) x-cloak @endif class="mt-12" role="tabpanel">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <span class="eyebrow">{{ $plot->size_label }}</span>
                                <h2 class="mt-2 text-3xl font-semibold text-primary">{{ $plot->title }}</h2>
                                <p class="mt-2 max-w-2xl text-ink/70">{{ $plot->short_description }}</p>
                            </div>
                            @if ($plot->activePlans->count() > 1)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($plot->activePlans as $i => $plan)
                                        <button type="button" @click="plan = {{ $i }}" :class="plan === {{ $i }} ? 'bg-accent text-primary' : 'bg-white text-primary ring-1 ring-primary/10'" class="rounded-full px-4 py-2 text-xs font-semibold transition">{{ $plan->name }}</button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @foreach ($plot->activePlans as $i => $plan)
                            @php($summary = $plan->summary())
                            <div x-show="plan === {{ $i }}" @if ($i) x-cloak @endif class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px]">
                                <x-front.payment-timeline :plan="$plan" />
                                <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                                    <div class="card p-6">
                                        <h3 class="font-sans text-lg font-semibold text-primary">{{ $plan->name }}</h3>
                                        @if ($plan->description)
                                            <p class="mt-1.5 text-sm text-ink/70">{{ $plan->description }}</p>
                                        @endif
                                        <dl class="mt-5 space-y-2.5 text-sm">
                                            <div class="flex justify-between"><dt class="text-ink/70">Total price</dt><dd class="font-semibold text-primary">{{ money($summary->totalPrice) }}</dd></div>
                                            @if ($plan->full_payment_price)
                                                <div class="flex justify-between"><dt class="text-ink/70">Lump-sum price</dt><dd class="font-semibold text-primary">{{ money($plan->full_payment_price) }}</dd></div>
                                            @endif
                                            <div class="flex justify-between"><dt class="text-ink/70">Duration</dt><dd class="font-semibold text-primary">{{ $summary->durationMonths }} months</dd></div>
                                            <div class="flex justify-between"><dt class="text-ink/70">Installments</dt><dd class="font-semibold text-primary">{{ $summary->installmentCount }}</dd></div>
                                            @if ($plot->development_charges)
                                                <div class="flex justify-between border-t border-primary/8 pt-2.5"><dt class="text-ink/70">Development charges</dt><dd class="font-semibold text-primary">{{ money($plot->development_charges) }}</dd></div>
                                            @endif
                                            @if ($plot->possession_charges)
                                                <div class="flex justify-between"><dt class="text-ink/70">Possession charges</dt><dd class="font-semibold text-primary">{{ money($plot->possession_charges) }}</dd></div>
                                            @endif
                                        </dl>
                                        <div class="mt-6 grid gap-2.5">
                                            @if ($plot->isBookable())
                                                <a href="{{ route('book-plot', ['plot' => $plot->slug, 'plan' => $plan->slug]) }}" class="btn-primary">Book This Plan</a>
                                            @endif
                                            <a href="{{ route('plots.show', $plot) }}" class="btn-outline">{{ $plot->size_label }} Details</a>
                                        </div>
                                    </div>
                                    @if ($plan->terms)
                                        <p class="flex gap-2 rounded-brand bg-white p-4 text-xs text-ink/70 ring-1 ring-primary/8"><x-icon name="info" class="size-4 text-accent" /> {{ $plan->terms }}</p>
                                    @endif
                                </aside>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Comparison table --}}
        <section data-defer class="bg-white py-16 sm:py-24">
            <div class="container-x">
                <x-front.section-heading eyebrow="Compare" heading="All Plans Side by Side" />
                <div class="mt-10 overflow-x-auto rounded-brand-lg border border-primary/8 shadow-soft">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Plan</th>
                                <th class="px-5 py-4 font-semibold">Total</th>
                                <th class="px-5 py-4 font-semibold">Booking</th>
                                <th class="px-5 py-4 font-semibold">Advance</th>
                                <th class="px-5 py-4 font-semibold">Installment</th>
                                <th class="px-5 py-4 font-semibold">Milestones</th>
                                <th class="px-5 py-4 font-semibold">End</th>
                                <th class="px-5 py-4 font-semibold">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/6">
                            @foreach ($plots as $plot)
                                @foreach ($plot->activePlans as $plan)
                                    @php($s = $plan->summary())
                                    <tr class="hover:bg-surface">
                                        <td class="px-5 py-4"><span class="block font-semibold text-primary">{{ $plot->size_label }}</span><span class="text-xs text-ink/70">{{ $plan->name }}</span></td>
                                        <td class="px-5 py-4 font-semibold text-primary">{{ money($s->totalPrice) }}</td>
                                        <td class="px-5 py-4">{{ money($s->amountFor('booking')) }}</td>
                                        <td class="px-5 py-4">{{ money($s->amountFor('advance', 'full_payment')) }}</td>
                                        <td class="px-5 py-4">{{ money($s->installmentAmount) }} <span class="text-xs text-ink/70">× {{ $s->installmentCount }}</span></td>
                                        <td class="px-5 py-4">{{ money($s->amountFor('after_6_months', 'after_12_months', 'after_18_months', 'after_24_months', 'custom')) }}</td>
                                        <td class="px-5 py-4">{{ money($s->amountFor('end_payment')) }}</td>
                                        <td class="px-5 py-4">{{ $s->durationMonths }} mo</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section data-defer id="calculator" class="relative overflow-hidden bg-primary py-16 text-white sm:py-24">
            <div class="pointer-events-none absolute inset-0 bg-grid-light"></div>
            <div class="relative container-x">
                <x-front.section-heading eyebrow="Payment Calculator" heading="Calculate Your Payment Plan" :dark="true" />
                <x-front.calculator class="mt-12" :initial="$selected?->slug" />
            </div>
        </section>
    @endif

    @include('front.partials.cta-band')
@endsection
