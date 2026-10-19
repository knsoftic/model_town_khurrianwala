@extends('layouts.front')

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['Plot Sizes' => null]" />

    <section class="py-16 sm:py-24" x-data="{ category: 'all' }">
        <div class="container-x">
            @if ($page->content)
                <div class="prose-brand mx-auto mb-12 max-w-3xl text-center">{!! $page->content !!}</div>
            @endif

            @if ($categories->count() > 1)
                <div class="mb-10 flex flex-wrap justify-center gap-2">
                    <button type="button" @click="category = 'all'" :class="category === 'all' ? 'bg-primary text-white' : 'bg-white text-primary ring-1 ring-primary/10'" class="rounded-full px-5 py-2.5 text-sm font-semibold transition">All Plots</button>
                    @foreach ($categories as $category)
                        <button type="button" @click="category = @js($category)" :class="category === @js($category) ? 'bg-primary text-white' : 'bg-white text-primary ring-1 ring-primary/10'" class="rounded-full px-5 py-2.5 text-sm font-semibold transition">
                            {{ \App\Models\Plot::CATEGORIES[$category] ?? str($category)->headline() }}
                        </button>
                    @endforeach
                </div>
            @endif

            @if ($plots->isEmpty())
                <p class="rounded-brand-lg bg-white p-12 text-center text-ink/70">Plot sizes will be announced soon. Please contact our sales team for details.</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($plots as $plot)
                        <div x-show="category === 'all' || category === @js($plot->category)" x-transition.opacity>
                            <x-front.plot-card :plot="$plot" class="h-full" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Side-by-side comparison --}}
    @if ($plots->count() > 1)
        <section data-defer class="bg-white py-16 sm:py-24">
            <div class="container-x">
                <x-front.section-heading eyebrow="Compare" heading="Compare Plot Sizes at a Glance" subheading="Prices and payment milestones for every plot size, side by side." />
                <div class="mt-12 overflow-x-auto rounded-brand-lg border border-primary/8 shadow-soft">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Plot Size</th>
                                <th class="px-5 py-4 font-semibold">Total Price</th>
                                <th class="px-5 py-4 font-semibold">Booking</th>
                                <th class="px-5 py-4 font-semibold">Confirmation</th>
                                <th class="px-5 py-4 font-semibold">After 6 Months</th>
                                <th class="px-5 py-4 font-semibold">Installment</th>
                                <th class="px-5 py-4 font-semibold">Possession</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/6 bg-white">
                            @foreach ($plots as $plot)
                                <tr class="transition hover:bg-surface">
                                    <td class="px-5 py-4"><a href="{{ route('plots.show', $plot) }}" class="font-semibold text-primary hover:text-accent">{{ $plot->size_label }}</a></td>
                                    <td class="px-5 py-4 font-semibold text-primary">{{ money($plot->total_price ?? $plot->displayPrice()) }}</td>
                                    <td class="px-5 py-4">{{ money($plot->booking_amount) }}</td>
                                    <td class="px-5 py-4">{{ money($plot->down_payment) }}</td>
                                    <td class="px-5 py-4">{{ money($plot->after_6_months_payment) }}@if ($plot->after_12_months_payment) / {{ money($plot->after_12_months_payment) }}@endif</td>
                                    <td class="px-5 py-4">{{ money($plot->installment_amount) }} <span class="text-xs text-ink/70">{{ $plot->frequencySuffix() }}</span></td>
                                    <td class="px-5 py-4">{{ money($plot->end_payment) }}</td>
                                    <td class="px-5 py-4"><x-front.status-badge :status="$plot->status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($plots->contains('is_demo_price', true))
                    <p class="mt-4 text-center text-xs text-ink/70">Prices marked "Demo Price" are sample values for preview only.</p>
                @endif
            </div>
        </section>
    @endif

    @include('front.partials.cta-band')
@endsection
