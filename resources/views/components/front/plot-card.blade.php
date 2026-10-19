@props(['plot'])

@php
    $plan = $plot->activePlans->first();
    $rows = array_filter([
        ['Booking', $plot->booking_amount, null],
        ['Installment', $plot->installment_amount, $plot->frequencySuffix()],
        ['After 6 Months', $plot->after_6_months_payment, null],
        ['After 12 Months', $plot->after_12_months_payment, null],
        ['End Payment', $plot->end_payment, null],
    ], fn ($row) => $row[1] !== null);
    $whatsapp = setting_enabled('whatsapp_on_plot_cards', true)
        ? whatsapp_url(str_replace('{plot}', $plot->size_label, (string) setting('whatsapp_plot_message')))
        : null;
@endphp

<article {{ $attributes->class(['group flex flex-col overflow-hidden rounded-brand-lg border border-primary/8 bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift']) }}>
    <a href="{{ route('plots.show', $plot) }}" class="relative block aspect-[4/3] overflow-hidden bg-surface">
        @if ($plot->image)
            <img src="{{ media_url($plot->image) }}" alt="{{ $plot->title }}" loading="lazy" class="size-full object-cover transition duration-700 group-hover:scale-105">
        @endif
        <div class="absolute inset-x-0 top-0 flex items-start justify-between p-3.5">
            <x-front.status-badge :status="$plot->status" class="bg-white! shadow-sm" />
            @if ($plot->is_featured)
                <span class="inline-flex items-center gap-1 rounded-full bg-primary px-2.5 py-1 text-[11px] font-semibold text-accent">
                    <x-icon name="star" class="size-3" stroke="2.5" /> Featured
                </span>
            @endif
        </div>
        @if ($plot->is_demo_price)
            <span class="absolute right-3.5 bottom-3.5 rounded-full bg-primary/85 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-white uppercase">Demo Price</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-2xl font-semibold tracking-tight text-primary uppercase">{{ $plot->size_label }}</h3>
                <p class="mt-0.5 text-sm text-ink/60">{{ $plot->title }}@if ($plot->dimensions) · {{ $plot->dimensions }} ft @endif</p>
            </div>
            @if ($whatsapp)
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="grid size-10 shrink-0 place-items-center rounded-full bg-[#25D366]/10 text-[#1a9e4b] transition hover:bg-[#25D366] hover:text-white" aria-label="Ask about {{ $plot->size_label }} on WhatsApp">
                    <x-icon name="whatsapp" class="size-5" />
                </a>
            @endif
        </div>

        <div class="mt-5 rounded-brand bg-surface px-4 py-3.5">
            <p class="text-[11px] font-semibold tracking-wider text-ink/50 uppercase">Starting From</p>
            <p class="mt-0.5 font-heading text-2xl font-semibold text-primary">{{ money($plot->displayPrice()) }}</p>
        </div>

        <dl class="mt-4 divide-y divide-primary/6 text-sm">
            @foreach ($rows as [$label, $amount, $suffix])
                <div class="flex items-center justify-between gap-3 py-2.5">
                    <dt class="text-ink/60">{{ $label }}</dt>
                    <dd class="text-right font-semibold text-primary">{{ money($amount) }}@if ($suffix)<span class="ml-1 text-xs font-medium text-ink/50">{{ $suffix }}</span>@endif</dd>
                </div>
            @endforeach
        </dl>

        <div class="mt-auto grid grid-cols-2 gap-2.5 pt-6">
            <a href="{{ route('plots.show', $plot) }}#payment-plan" class="btn-outline !px-3 text-[13px]">View Payment Plan</a>
            @if ($plot->isBookable())
                <a href="{{ route('book-plot', ['plot' => $plot->slug, 'plan' => $plan?->slug]) }}" class="btn-primary !px-3 text-[13px]">Book This Plot</a>
            @else
                <span class="btn cursor-not-allowed bg-primary/5 !px-3 text-[13px] text-ink/50">{{ $plot->status->label() }}</span>
            @endif
            <a href="{{ route('plots.show', $plot) }}" class="col-span-2 inline-flex items-center justify-center gap-1.5 py-1.5 text-[13px] font-semibold text-primary/80 transition hover:text-accent">
                Get Details <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" />
            </a>
        </div>
    </div>
</article>
