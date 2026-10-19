@extends('layouts.admin')

@php
    $editing = $plan->exists;
    $currency = setting('currency_symbol', 'PKR');
    $prices = $plots->mapWithKeys(fn ($plot) => [$plot->id => ['total' => (float) $plot->total_price, 'lump' => (float) $plot->starting_price]]);
@endphp

@section('title', $editing ? 'Edit '.$plan->name : 'New Installment Plan')

@section('breadcrumbs')
    <a href="{{ route('admin.installment-plans.index') }}" class="hover:text-slate-900">Installment Plans</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $editing ? $plan->name : 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.installment-plans.update', $plan) : route('admin.installment-plans.store') }}"
          x-data="scheduleBuilder({ items: @js(old('items', $items)), total: @js((float) old('total_price', $plan->total_price)), types: @js($types), defaults: @js($defaults) })">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $plan->name : 'New Installment Plan'" description="Build the payment schedule stage by stage. Totals and percentages update as you type.">
            <x-slot:actions>
                @if ($editing)
                    <button type="submit" form="apply-plan" class="a-btn-secondary" title="Copy booking, installment and milestone amounts to the plot card"><x-icon name="clipboard-copy" class="size-4" /> Copy figures to plot card</button>
                @endif
                <button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save plan</button>
            </x-slot:actions>
        </x-admin.page-header>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                {{-- Schedule builder --}}
                <section class="a-card">
                    <div class="sticky top-16 z-10 rounded-t-xl border-b border-slate-200 bg-white/95 p-5 backdrop-blur">
                        <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                            <div><p class="text-xs text-slate-500">Total price</p><p class="text-lg font-bold text-slate-900">{{ $currency }} <span x-text="format(total)"></span></p></div>
                            <div><p class="text-xs text-slate-500">Scheduled</p><p class="text-lg font-bold text-slate-900">{{ $currency }} <span x-text="format(scheduled)"></span></p></div>
                            <div>
                                <p class="text-xs text-slate-500">Remaining</p>
                                <p class="text-lg font-bold" :class="balanced ? 'text-emerald-600' : 'text-amber-600'">{{ $currency }} <span x-text="format(remaining)"></span></p>
                            </div>
                            <div><p class="text-xs text-slate-500">Installments</p><p class="text-lg font-bold text-slate-900"><span x-text="installmentCount"></span> × <span x-text="format(installmentAmount)"></span></p></div>
                        </div>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full transition-all" :class="allocatedPercent > 100.5 ? 'bg-rose-500' : (balanced ? 'bg-emerald-500' : 'bg-gold-500')" :style="`width: ${Math.min(100, allocatedPercent)}%`"></div>
                            </div>
                            <span class="w-44 text-right text-xs font-semibold text-slate-600"><span x-text="format(allocatedPercent)"></span>% paid · <span x-text="format(remainingPercent)"></span>% left</span>
                        </div>
                        <p class="mt-2 text-xs" :class="balanced ? 'text-emerald-700' : 'text-amber-700'">
                            <span x-show="balanced">The schedule adds up exactly to the total price.</span>
                            <span x-show="!balanced && remaining > 0">The schedule is short of the total price — add a stage or use “Fill remaining” on the last one.</span>
                            <span x-show="!balanced && remaining < 0">The schedule is more than the total price — reduce an amount.</span>
                            · Duration: <strong x-text="duration"></strong> months
                        </p>
                    </div>

                    <div class="p-5">
                        <div class="mb-4 flex flex-wrap items-end gap-2">
                            <div class="min-w-52">
                                <label class="a-label" for="new-stage-type">Add a payment stage</label>
                                <select id="new-stage-type" x-model="newType" class="a-input">
                                    @foreach ($types as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" @click="add()" class="a-btn-gold"><x-icon name="plus" class="size-4" /> Add stage</button>
                            <button type="button" @click="loadTemplate()" class="a-btn-ghost ml-auto"><x-icon name="wand-sparkles" class="size-4" /> Load standard template</button>
                        </div>

                        <ol x-ref="list" class="space-y-3">
                            <template x-for="(item, index) in items" :key="item.key">
                                <li class="rounded-xl border border-slate-200 bg-white shadow-xs">
                                    <div class="flex flex-wrap items-start gap-3 p-3 sm:flex-nowrap">
                                        <div class="flex items-center gap-2 pt-7">
                                            <span data-drag class="cursor-grab text-slate-400 hover:text-slate-700" title="Drag to reorder"><x-icon name="grip-vertical" class="size-4" /></span>
                                            <span class="grid size-7 place-items-center rounded-full bg-navy-900 text-[11px] font-bold text-gold-400" x-text="String(index + 1).padStart(2, '0')"></span>
                                        </div>
                                        <div class="grid flex-1 gap-3 sm:grid-cols-12">
                                            <div class="sm:col-span-5">
                                                <label class="a-label">Type</label>
                                                <select class="a-input" x-model="item.type" @change="changeType(item)" :name="`items[${index}][type]`">
                                                    @foreach ($types as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="sm:col-span-7">
                                                <label class="a-label">Title</label>
                                                <input data-focus class="a-input" x-model="item.title" :name="`items[${index}][title]`" required>
                                            </div>
                                            <div class="sm:col-span-5">
                                                <label class="a-label">Amount <span x-show="['monthly','quarterly','half_yearly'].includes(item.type)">(each)</span></label>
                                                <div class="flex rounded-lg shadow-xs">
                                                    <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-2 text-xs text-slate-500">{{ $currency }}</span>
                                                    <input type="number" min="0" step="1" class="a-input rounded-l-none" x-model.number="item.amount" @input="syncPercent(item)" :name="`items[${index}][amount]`" required>
                                                </div>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="a-label">% of total</label>
                                                <input type="number" min="0" max="100" step="0.01" class="a-input" x-model.number="item.percentage" @input="syncAmount(item)">
                                            </div>
                                            <div class="sm:col-span-4" x-show="['monthly','quarterly','half_yearly'].includes(item.type)">
                                                <label class="a-label">How many times</label>
                                                <input type="number" min="1" max="600" class="a-input" x-model.number="item.occurrences" @input="syncPercent(item)">
                                            </div>
                                            <input type="hidden" :name="`items[${index}][occurrences]`" :value="['monthly','quarterly','half_yearly'].includes(item.type) ? item.occurrences : 1">
                                            <div class="sm:col-span-12 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                                <span>Line total: <strong class="text-slate-800">{{ $currency }} <span x-text="format(lineTotal(item))"></span></strong></span>
                                                <span x-show="item.due_label" x-text="item.due_label"></span>
                                                <button type="button" class="font-semibold text-gold-700 hover:underline" @click="fillRemaining(item)">Fill remaining</button>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-0.5 pt-6">
                                            <button type="button" class="a-icon-btn" title="More details" @click="item.open = !item.open"><x-icon name="sliders-horizontal" class="size-4" /></button>
                                            <button type="button" class="a-icon-btn" title="Move up" @click="move(index, -1)"><x-icon name="arrow-up" class="size-4" /></button>
                                            <button type="button" class="a-icon-btn" title="Move down" @click="move(index, 1)"><x-icon name="arrow-down" class="size-4" /></button>
                                            <button type="button" class="a-icon-btn" title="Duplicate" @click="duplicate(index)"><x-icon name="copy" class="size-4" /></button>
                                            <button type="button" class="a-icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="Remove" @click="remove(index)"><x-icon name="trash" class="size-4" /></button>
                                        </div>
                                    </div>
                                    <div x-show="item.open" x-collapse class="grid gap-3 border-t border-slate-100 bg-slate-50/60 p-3 sm:grid-cols-3">
                                        <div>
                                            <label class="a-label">Due after (months)</label>
                                            <input type="number" min="0" max="600" class="a-input" x-model="item.due_after_months" :name="`items[${index}][due_after_months]`" placeholder="0 = at booking">
                                        </div>
                                        <div>
                                            <label class="a-label">Due label (shown to visitors)</label>
                                            <input class="a-input" x-model="item.due_label" :name="`items[${index}][due_label]`" placeholder="e.g. Within 30 days of booking">
                                        </div>
                                        <div>
                                            <label class="a-label">Note</label>
                                            <input class="a-input" x-model="item.description" :name="`items[${index}][description]`" placeholder="Optional">
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ol>

                        <div x-show="!items.length" class="rounded-xl border-2 border-dashed border-slate-200 px-6 py-10 text-center text-sm text-slate-500">
                            No payment stages yet. Add one above, or load the standard template (down payment, monthly, 6-month installments, on registry).
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <x-admin.card title="Plan details">
                    <div class="space-y-4">
                        <div>
                            <label for="f-plot_id" class="a-label">Plot size <span class="text-rose-500">*</span></label>
                            <select id="f-plot_id" name="plot_id" class="a-input" required
                                    @change="const p = @js($prices)[$event.target.value]; if (p && !total) { total = p.total; $refs.lump.value = p.lump || ''; }">
                                <option value="">Select plot size</option>
                                @foreach ($plots as $plot)
                                    <option value="{{ $plot->id }}" @selected((string) old('plot_id', $plan->plot_id) === (string) $plot->id)>{{ $plot->size_label }}</option>
                                @endforeach
                            </select>
                            @error('plot_id')<p class="a-error">{{ $message }}</p>@enderror
                        </div>
                        <x-admin.input name="name" label="Plan name" :value="$plan->name" required placeholder="5 Marla Easy Installment Plan" />
                        <x-admin.input name="slug" label="URL slug (optional)" :value="$plan->slug" placeholder="Created from the name" />
                        <div>
                            <label for="f-total_price" class="a-label">Total price <span class="text-rose-500">*</span></label>
                            <div class="flex rounded-lg shadow-xs">
                                <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">{{ $currency }}</span>
                                <input id="f-total_price" type="number" min="0" step="1" name="total_price" x-model.number="total" class="a-input rounded-l-none" required>
                            </div>
                            @error('total_price')<p class="a-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="f-full_payment_price" class="a-label">Full / lump-sum payment price</label>
                            <div class="flex rounded-lg shadow-xs">
                                <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">{{ $currency }}</span>
                                <input id="f-full_payment_price" x-ref="lump" type="number" min="0" step="1" name="full_payment_price" value="{{ old('full_payment_price', $plan->full_payment_price ? (float) $plan->full_payment_price : '') }}" class="a-input rounded-l-none">
                            </div>
                        </div>
                        <x-admin.input name="duration_months" type="number" min="0" label="Duration (months)" :value="$plan->duration_months" help="Leave blank to calculate from the schedule." />
                        <x-admin.textarea name="description" label="Short description" :value="$plan->description" rows="2" />
                        <x-admin.textarea name="terms" label="Terms / note" :value="$plan->terms" rows="3" help="Shown under the schedule on the website." />
                    </div>
                </x-admin.card>

                <x-admin.card title="Visibility">
                    <div class="space-y-4">
                        <x-admin.toggle name="is_active" label="Show on website" :checked="$plan->is_active" />
                        <x-admin.toggle name="is_default" label="Default plan for this plot size" :checked="$plan->is_default" help="Used on the plot card, calculator and homepage." />
                        <x-admin.toggle name="is_featured" label="Featured" :checked="$plan->is_featured" />
                        <x-admin.toggle name="is_demo" label="Demo schedule" :checked="$plan->is_demo" help="Adds “Demo schedule for preview only”." />
                        <x-admin.input name="sort_order" type="number" min="0" label="Order within plot size" :value="$plan->sort_order" />
                    </div>
                </x-admin.card>

                @if ($editing)
                    <x-admin.card title="More actions">
                        <div class="flex flex-wrap gap-2">
                            @if ($plan->plot)<a href="{{ route('plots.show', $plan->plot) }}#payment-plan" target="_blank" class="a-btn-secondary a-btn-sm"><x-icon name="external-link" class="size-3.5" /> View on website</a>@endif
                            <button type="submit" form="duplicate-plan" class="a-btn-secondary a-btn-sm"><x-icon name="copy" class="size-3.5" /> Duplicate</button>
                            <button type="button" class="a-btn-ghost a-btn-sm text-rose-600" @click="$store.confirm.ask({ action: @js(route('admin.installment-plans.destroy', $plan)), title: 'Delete this plan?' })"><x-icon name="trash" class="size-3.5" /> Delete</button>
                        </div>
                    </x-admin.card>
                @endif
            </div>
        </div>

        <x-admin.form-actions :cancel="route('admin.installment-plans.index')" label="Save Plan" />
    </form>

    @if ($editing)
        <form id="apply-plan" method="POST" action="{{ route('admin.installment-plans.apply', $plan) }}" class="hidden">@csrf</form>
        <form id="duplicate-plan" method="POST" action="{{ route('admin.installment-plans.duplicate', $plan) }}" class="hidden">@csrf</form>
    @endif
@endsection
