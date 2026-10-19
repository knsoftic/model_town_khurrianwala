@extends('layouts.admin')

@section('title', 'Installment Plans')

@section('content')
    <x-admin.page-header title="Installment Plans" description="Each plot size can have several plans. The default plan drives the plot card, calculator and homepage schedule.">
        <x-slot:actions>
            <a href="{{ route('admin.installment-plans.schedules') }}" class="a-btn-secondary"><x-icon name="list-ordered" class="size-4" /> Schedule overview</a>
            <a href="{{ route('admin.installment-plans.create', ['plot' => request('plot')]) }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> New Plan</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-[1fr_220px_auto]">
        <x-admin.input name="q" :value="request('q')" placeholder="Search plan name…" />
        <x-admin.select name="plot" :value="request('plot')" :options="$plots" placeholder="All plot sizes" />
        <div class="flex gap-2"><button class="a-btn-primary"><x-icon name="search" class="size-4" /> Filter</button>@if (request()->hasAny(['q', 'plot']))<a href="{{ route('admin.installment-plans.index') }}" class="a-btn-ghost">Reset</a>@endif</div>
    </form>

    <x-admin.card :padding="false">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead><tr><th>Plan</th><th>Total</th><th>Schedule</th><th>Installments</th><th class="text-center">Website</th><th class="text-center">Featured</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($plans as $plan)
                        @php($s = $plan->summary())
                        <tr>
                            <td>
                                <a href="{{ route('admin.installment-plans.edit', $plan) }}" class="font-semibold text-slate-900 hover:text-gold-700">{{ $plan->name }}</a>
                                <span class="block text-xs text-slate-500">{{ $plan->plot?->size_label ?? 'Deleted plot' }} · {{ count($s->stages) }} stages · {{ $s->durationMonths }} months</span>
                                <span class="mt-1 flex gap-1">
                                    @if ($plan->is_default)<span class="a-badge bg-gold-100 text-gold-700 ring-gold-500/30">Default</span>@endif
                                    @if ($plan->is_demo)<span class="a-badge bg-amber-50 text-amber-700 ring-amber-600/20">Demo</span>@endif
                                </span>
                            </td>
                            <td class="whitespace-nowrap font-semibold text-slate-900">{{ money($s->totalPrice, false) }}</td>
                            <td class="min-w-44">
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100"><div @class(['h-full rounded-full', 'bg-emerald-500' => $s->isBalanced(), 'bg-amber-500' => ! $s->isBalanced()]) style="width: {{ min(100, $s->allocatedPercent) }}%"></div></div>
                                <span @class(['text-xs', 'text-emerald-700' => $s->isBalanced(), 'text-amber-700' => ! $s->isBalanced()])>
                                    {{ $s->isBalanced() ? 'Balanced' : money(abs($s->remaining), false).($s->remaining > 0 ? ' unallocated' : ' over total') }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap text-slate-700">{{ $s->installmentCount }} × {{ money($s->installmentAmount, false) }}</td>
                            <td class="text-center"><x-admin.switch :url="route('admin.toggle', ['installment-plans', $plan->id])" :value="$plan->is_active" /></td>
                            <td class="text-center"><x-admin.switch :url="route('admin.toggle', ['installment-plans', $plan->id])" :value="$plan->is_featured" field="is_featured" /></td>
                            <td>
                                <x-admin.row-actions :edit="route('admin.installment-plans.edit', $plan)" :delete="route('admin.installment-plans.destroy', $plan)" :delete-label="$plan->name">
                                    <form method="POST" action="{{ route('admin.installment-plans.duplicate', $plan) }}">@csrf<button class="a-icon-btn" title="Duplicate"><x-icon name="copy" class="size-4" /></button></form>
                                </x-admin.row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-admin.empty icon="calendar-range" title="No installment plans" description="Create a plan to publish a payment schedule."><a href="{{ route('admin.installment-plans.create') }}" class="a-btn-gold">New Plan</a></x-admin.empty></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <div class="mt-4">{{ $plans->links() }}</div>
@endsection
