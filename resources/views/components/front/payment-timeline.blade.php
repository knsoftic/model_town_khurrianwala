@props(['plan', 'compact' => false])

@php
    $summary = $plan->summary();
@endphp

<div {{ $attributes->class(['relative']) }}>
    <ol class="relative space-y-3 sm:space-y-4">
        @foreach ($summary->stages as $i => $stage)
            @php($item = $stage['item'])
            <li class="relative flex gap-4 sm:gap-6">
                {{-- Step marker + connector --}}
                <div class="relative flex flex-col items-center">
                    <span @class([
                        'relative z-10 grid size-12 shrink-0 place-items-center rounded-full font-heading text-base font-semibold ring-4 ring-white sm:size-14 sm:text-lg',
                        'bg-primary text-accent' => ! $loop->last,
                        'bg-accent text-primary' => $loop->last,
                    ])>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    @unless ($loop->last)
                        <span class="absolute top-12 bottom-[-1rem] w-px border-l-2 border-dashed border-primary/15 sm:top-14 sm:bottom-[-1.25rem]"></span>
                        <span class="absolute bottom-[-0.95rem] z-10 grid size-5 place-items-center rounded-full bg-white text-accent sm:bottom-[-1.2rem]">
                            <x-icon name="chevron-down" class="size-4" stroke="2.5" />
                        </span>
                    @endunless
                </div>

                <div class="flex-1 rounded-brand border border-primary/8 bg-white p-4 shadow-soft sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="flex items-center gap-2 text-[11px] font-semibold tracking-wider text-accent-ink uppercase">
                                <x-icon :name="$item->type->icon()" class="size-3.5" /> {{ $item->type->label() }}
                            </p>
                            <h3 class="mt-1 font-sans text-base font-semibold text-primary sm:text-lg">{{ $item->title }}</h3>
                            @if ($item->due_label)
                                <p class="mt-0.5 text-sm text-ink/70">{{ $item->due_label }}</p>
                            @endif
                            @if ($item->description && ! $compact)
                                <p class="mt-1.5 text-sm text-ink/70">{{ $item->description }}</p>
                            @endif
                        </div>
                        <div class="shrink-0 sm:text-right">
                            <p class="font-heading text-xl font-semibold text-primary sm:text-2xl">{{ money($item->amount) }}</p>
                            @if ($item->occurrences > 1)
                                <p class="text-xs font-medium text-ink/70">{{ $item->type->repeatText($item->occurrences) }} = <span class="text-primary">{{ money($stage['line_total']) }}</span></p>
                            @endif
                        </div>
                    </div>
                    @unless ($compact)
                        <div class="mt-4 flex items-center gap-3">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-primary/6">
                                <div class="h-full rounded-full bg-gradient-to-r from-accent/70 to-accent" style="width: {{ min(100, $stage['cumulative_percent']) }}%"></div>
                            </div>
                            <span class="shrink-0 text-[11px] font-semibold text-ink/70">{{ rtrim(rtrim(number_format($stage['percent'], 1), '0'), '.') }}% · {{ rtrim(rtrim(number_format($stage['cumulative_percent'], 1), '0'), '.') }}% paid</span>
                        </div>
                    @endunless
                </div>
            </li>
        @endforeach
    </ol>

    <div class="relative mt-6 overflow-hidden rounded-brand-lg bg-primary p-6 text-white sm:p-7">
        <div class="absolute inset-0 bg-grid-light opacity-60"></div>
        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-[0.2em] text-accent uppercase">Total</p>
                <p class="mt-1 font-heading text-3xl font-semibold sm:text-4xl">{{ money($summary->totalPrice) }}</p>
                @if ($plan->full_payment_price)
                    <p class="mt-1 text-sm text-white/70">Lump-sum price: <span class="font-semibold text-white">{{ money($plan->full_payment_price) }}</span></p>
                @endif
            </div>
            <dl class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm sm:text-right">
                @if ($summary->durationMonths)
                    <div><dt class="text-white/60">Duration</dt><dd class="font-semibold">{{ $summary->durationMonths }} months</dd></div>
                @endif
                @if ($summary->installmentCount)
                    <div><dt class="text-white/60">Installments</dt><dd class="font-semibold">{{ $summary->installmentCount }} × {{ money($summary->installmentAmount) }}</dd></div>
                @endif
            </dl>
        </div>
        @if ($plan->is_demo)
            <p class="relative mt-4 text-xs text-white/55">Demo schedule for preview only — not real company pricing.</p>
        @endif
    </div>
</div>
