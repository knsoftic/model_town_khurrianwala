@props(['initial' => null])

<div x-data="calculator(@js(route('api.plots.index')), @js($initial))" {{ $attributes->class(['overflow-hidden rounded-brand-lg bg-white text-ink shadow-2xl']) }}>
    <div class="grid lg:grid-cols-[0.95fr_1.05fr]">
        {{-- Selection --}}
        <div class="border-b border-primary/8 bg-surface p-6 sm:p-8 lg:border-r lg:border-b-0">
            <p class="flex items-center gap-2 text-xs font-semibold tracking-wider text-accent uppercase">
                <span class="grid size-6 place-items-center rounded-full bg-primary text-[11px] text-white">1</span> Select plot size
            </p>

            <template x-if="loading">
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <template x-for="n in 4" :key="n"><div class="h-20 animate-pulse rounded-brand bg-primary/8"></div></template>
                </div>
            </template>
            <p x-cloak x-show="failed" class="mt-5 rounded-brand bg-rose-50 p-4 text-sm text-rose-700">The calculator could not load right now. Please refresh the page or contact us.</p>

            <div x-cloak x-show="!loading && !failed" class="mt-5 grid grid-cols-2 gap-3">
                <template x-for="item in plots" :key="item.slug">
                    <button type="button" @click="plotSlug = item.slug"
                            :class="plotSlug === item.slug ? 'border-accent bg-white shadow-md ring-2 ring-accent/30' : 'border-primary/10 bg-white/60 hover:border-accent/60 hover:bg-white'"
                            class="rounded-brand border p-4 text-left transition">
                        <span class="flex items-center justify-between gap-2">
                            <span class="font-heading text-lg font-semibold text-primary" x-text="item.size_label"></span>
                            <span x-show="plotSlug === item.slug" class="grid size-5 place-items-center rounded-full bg-accent text-primary"><x-icon name="check" class="size-3" stroke="3" /></span>
                        </span>
                        <span class="mt-1 block text-xs text-ink/55" x-text="item.prices.total_price ? money(item.prices.total_price) : item.title"></span>
                    </button>
                </template>
            </div>

            <div x-cloak x-show="plot && plot.plans.length > 1" class="mt-7">
                <p class="flex items-center gap-2 text-xs font-semibold tracking-wider text-accent uppercase">
                    <span class="grid size-6 place-items-center rounded-full bg-primary text-[11px] text-white">2</span> Choose installment plan
                </p>
                <div class="mt-4 space-y-2">
                    <template x-for="option in (plot?.plans ?? [])" :key="option.slug">
                        <label class="flex cursor-pointer items-center gap-3 rounded-brand border bg-white p-3.5 transition"
                               :class="planSlug === option.slug ? 'border-accent ring-2 ring-accent/25' : 'border-primary/10 hover:border-accent/50'">
                            <input type="radio" class="size-4 accent-[var(--brand-accent)]" :value="option.slug" x-model="planSlug">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-primary" x-text="option.name"></span>
                                <span class="block text-xs text-ink/55" x-text="`${option.duration_months} months · ${money(option.total_price)}`"></span>
                            </span>
                        </label>
                    </template>
                </div>
            </div>

            <div class="mt-8 flex items-start gap-3 rounded-brand bg-white p-4 text-xs text-ink/60 ring-1 ring-primary/8">
                <x-icon name="info" class="mt-0.5 size-4 text-accent" />
                <p>Amounts come directly from the installment plans managed by our sales office. Development and possession charges, where applicable, are shown separately on each plot page.</p>
            </div>
        </div>

        {{-- Results --}}
        <div class="p-6 sm:p-8" aria-live="polite">
            <template x-if="plan">
                <div>
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold tracking-wider text-ink/50 uppercase" x-text="plot.size_label + ' · ' + plan.name"></p>
                            <p class="mt-1 font-heading text-3xl font-semibold text-primary sm:text-4xl" x-text="money(plan.total_price)"></p>
                            <p class="mt-1 text-sm text-ink/60">Total price</p>
                        </div>
                        <span x-show="plot.is_demo_price" class="rounded-full bg-accent/15 px-3 py-1 text-[11px] font-semibold tracking-wide text-primary uppercase">Demo Price</span>
                    </div>

                    <dl class="mt-6 divide-y divide-primary/6 rounded-brand border border-primary/8">
                        <template x-for="row in rows" :key="row.label">
                            <div class="flex items-center justify-between gap-4 px-4 py-3.5">
                                <dt class="text-sm text-ink/65" x-text="row.label"></dt>
                                <dd class="text-right">
                                    <span class="block font-semibold text-primary" x-text="money(row.value)"></span>
                                    <span x-show="row.note" class="block text-[11px] text-ink/50" x-text="row.note"></span>
                                </dd>
                            </div>
                        </template>
                        <div class="flex items-center justify-between gap-4 px-4 py-3.5">
                            <dt class="text-sm text-ink/65">Total Duration</dt>
                            <dd class="font-semibold text-primary" x-text="duration"></dd>
                        </div>
                        <div x-show="plan.full_payment_price" class="flex items-center justify-between gap-4 bg-surface px-4 py-3.5">
                            <dt class="text-sm text-ink/65">Or pay in full (lump sum)</dt>
                            <dd class="font-semibold text-primary" x-text="money(plan.full_payment_price)"></dd>
                        </div>
                    </dl>

                    <div class="mt-6 grid gap-2.5 sm:grid-cols-2">
                        <a :href="plan.book_url" x-show="plot.status.bookable" class="btn-primary">Book This Plan <x-icon name="arrow-right" class="size-4" /></a>
                        <a :href="plot.url + '#payment-plan'" class="btn-outline">View Full Schedule</a>
                    </div>
                </div>
            </template>
            <template x-if="!plan && !loading && !failed">
                <p class="py-16 text-center text-sm text-ink/55">Select a plot size to see its payment plan.</p>
            </template>
            <template x-if="loading">
                <div class="space-y-3">
                    <div class="h-10 w-2/3 animate-pulse rounded-brand bg-primary/8"></div>
                    <template x-for="n in 6" :key="n"><div class="h-12 animate-pulse rounded-brand bg-primary/5"></div></template>
                </div>
            </template>
        </div>
    </div>
</div>
