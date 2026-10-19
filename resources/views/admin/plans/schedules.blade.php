@extends('layouts.admin')

@section('title', 'Payment Schedules')

@section('content')
    <x-admin.page-header title="Payment Schedules" description="Every plot size's plans at a glance — spot schedules that don't add up to the total price." />

    <div class="space-y-6">
        @foreach ($plots as $plot)
            <x-admin.card :title="$plot->size_label" :description="$plot->title" :padding="false">
                <x-slot:actions>
                    <a href="{{ route('admin.installment-plans.create', ['plot' => $plot->id]) }}" class="a-btn-ghost a-btn-sm"><x-icon name="plus" class="size-3.5" /> Add plan</a>
                </x-slot:actions>
                @forelse ($plot->installmentPlans as $plan)
                    @php($s = $plan->summary())
                    <div class="border-b border-slate-100 p-5 last:border-0">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <a href="{{ route('admin.installment-plans.edit', $plan) }}" class="font-semibold text-slate-900 hover:text-gold-700">{{ $plan->name }}</a>
                                @if ($plan->is_default)<span class="a-badge ml-1 bg-gold-100 text-gold-700 ring-gold-500/30">Default</span>@endif
                                @unless ($plan->is_active)<span class="a-badge ml-1 bg-slate-100 text-slate-500 ring-slate-300">Hidden</span>@endunless
                            </div>
                            <span @class(['a-badge', 'bg-emerald-100 text-emerald-800 ring-emerald-600/20' => $s->isBalanced(), 'bg-amber-100 text-amber-800 ring-amber-600/20' => ! $s->isBalanced()])>
                                {{ $s->isBalanced() ? 'Adds up to total' : money(abs($s->remaining), false).($s->remaining > 0 ? ' short' : ' over') }}
                            </span>
                        </div>
                        <div class="mt-4 flex gap-1 overflow-x-auto pb-1">
                            @foreach ($s->stages as $i => $stage)
                                <div class="min-w-36 flex-1 rounded-lg border border-slate-200 bg-slate-50 p-3" style="flex-grow: {{ max(1, $stage['percent']) }}">
                                    <p class="text-[10px] font-semibold tracking-wide text-gold-700 uppercase">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} · {{ $stage['item']->type->label() }}</p>
                                    <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $stage['item']->title }}</p>
                                    <p class="text-xs text-slate-600">{{ money($stage['item']->amount, false) }}@if ($stage['item']->occurrences > 1) × {{ $stage['item']->occurrences }}@endif</p>
                                    <p class="text-[11px] text-slate-400">{{ rtrim(rtrim(number_format($stage['percent'], 1), '0'), '.') }}% of total</p>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-slate-500">Total {{ money($s->totalPrice, false) }} · {{ $s->installmentCount }} installments · {{ $s->durationMonths }} months</p>
                    </div>
                @empty
                    <x-admin.empty icon="calendar-range" title="No plans for this plot size" />
                @endforelse
            </x-admin.card>
        @endforeach
    </div>
@endsection
