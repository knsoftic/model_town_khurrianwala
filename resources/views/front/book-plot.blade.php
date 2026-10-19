@extends('layouts.front')

@php
    $catalog = \App\Http\Resources\PlotResource::collection($plots)->resolve();
    $showCnic = setting_enabled('booking_show_cnic');
    $requireCnic = setting_enabled('booking_require_cnic');
@endphp

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['Book Your Plot' => null]" />

    <section class="py-14 sm:py-20"
             x-data="booking(@js($catalog), @js(old('plot_id', $selectedPlot?->id)), @js(old('installment_plan_id', $selectedPlan?->id)))">
        <div class="container-x">
            @if (session('booking_reference'))
                <div class="mx-auto max-w-2xl rounded-brand-lg bg-white p-10 text-center shadow-soft">
                    <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-500 text-white"><x-icon name="check" class="size-8" stroke="2.5" /></span>
                    <h2 class="mt-5 text-3xl font-semibold text-primary">Booking Request Received</h2>
                    <p class="mt-3 text-ink/70">{{ session('form_success') }}</p>
                    <p class="mt-6 inline-block rounded-brand bg-surface px-5 py-3 text-sm">Reference number: <span class="font-semibold text-primary">{{ session('booking_reference') }}</span></p>
                </div>
            @elseif ($plots->isEmpty())
                <div class="mx-auto max-w-2xl rounded-brand-lg bg-white p-10 text-center shadow-soft">
                    <h2 class="text-2xl font-semibold text-primary">No plots are open for booking right now</h2>
                    <p class="mt-3 text-ink/70">Please contact our sales team to join the waiting list.</p>
                    <a href="{{ route('contact') }}" class="btn-primary mt-6">Contact Sales</a>
                </div>
            @else
                <div class="grid gap-10 lg:grid-cols-[1fr_400px]">
                    <form x-data="ajaxForm" @submit.prevent="submit" action="{{ route('book-plot.store') }}" method="POST" novalidate class="card p-6 sm:p-8">
                        @csrf
                        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                        <div x-cloak x-show="success" class="py-10 text-center">
                            <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-500 text-white"><x-icon name="check" class="size-8" stroke="2.5" /></span>
                            <h2 class="mt-5 text-3xl font-semibold text-primary">Booking Request Received</h2>
                            <p class="mx-auto mt-3 max-w-lg text-ink/70" x-text="success"></p>
                            <p x-show="reference" class="mt-6 inline-block rounded-brand bg-surface px-5 py-3 text-sm">Reference number: <span class="font-semibold text-primary" x-text="reference"></span></p>
                            <div class="mt-8 flex flex-wrap justify-center gap-3">
                                <a href="{{ route('home') }}" class="btn-outline">Back to Home</a>
                                @if (whatsapp_url())
                                    <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" class="btn-whatsapp"><x-icon name="whatsapp" class="size-4" /> Chat on WhatsApp</a>
                                @endif
                            </div>
                        </div>

                        <div x-show="!success" class="space-y-8">
                            <fieldset>
                                <legend class="flex items-center gap-2 text-lg font-semibold text-primary"><span class="grid size-7 place-items-center rounded-full bg-primary text-xs text-white">1</span> Plot &amp; Plan</legend>
                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="b-plot" class="field-label">Plot size <span class="text-rose-500">*</span></label>
                                        <select id="b-plot" name="plot_id" x-model="plotId" required class="field" :aria-invalid="!!errors.plot_id">
                                            <option value="">Select plot size</option>
                                            @foreach ($plots as $plot)
                                                <option value="{{ $plot->id }}">{{ $plot->size_label }} — {{ money($plot->displayPrice()) }}</option>
                                            @endforeach
                                        </select>
                                        <p x-show="errors.plot_id" x-text="errors.plot_id" class="field-error"></p>
                                        @error('plot_id')<p class="field-error">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="b-plan" class="field-label">Installment plan</label>
                                        <select id="b-plan" name="installment_plan_id" x-model="planId" class="field" :disabled="!plot" :aria-invalid="!!errors.installment_plan_id">
                                            <option value="">Select a plot size first</option>
                                            <template x-for="option in (plot?.plans ?? [])" :key="option.id">
                                                <option :value="option.id" x-text="option.name" :selected="String(option.id) === String(planId)"></option>
                                            </template>
                                        </select>
                                        <p x-show="errors.installment_plan_id" x-text="errors.installment_plan_id" class="field-error"></p>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend class="flex items-center gap-2 text-lg font-semibold text-primary"><span class="grid size-7 place-items-center rounded-full bg-primary text-xs text-white">2</span> Your Details</legend>
                                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="b-name" class="field-label">Full name <span class="text-rose-500">*</span></label>
                                        <input id="b-name" name="name" type="text" required autocomplete="name" class="field" value="{{ old('name') }}" :aria-invalid="!!errors.name">
                                        <p x-show="errors.name" x-text="errors.name" class="field-error"></p>
                                        @error('name')<p class="field-error">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="b-phone" class="field-label">Phone / WhatsApp <span class="text-rose-500">*</span></label>
                                        <input id="b-phone" name="phone" type="tel" required autocomplete="tel" placeholder="+92 3XX XXXXXXX" class="field" value="{{ old('phone') }}" :aria-invalid="!!errors.phone">
                                        <p x-show="errors.phone" x-text="errors.phone" class="field-error"></p>
                                        @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="b-email" class="field-label">Email</label>
                                        <input id="b-email" name="email" type="email" autocomplete="email" class="field" value="{{ old('email') }}" :aria-invalid="!!errors.email">
                                        <p x-show="errors.email" x-text="errors.email" class="field-error"></p>
                                    </div>
                                    <div>
                                        <label for="b-city" class="field-label">City</label>
                                        <input id="b-city" name="city" type="text" autocomplete="address-level2" class="field" value="{{ old('city') }}">
                                    </div>
                                    @if ($showCnic)
                                        <div>
                                            <label for="b-cnic" class="field-label">CNIC @if ($requireCnic)<span class="text-rose-500">*</span>@else<span class="text-ink/70">(optional)</span>@endif</label>
                                            <input id="b-cnic" name="cnic" type="text" inputmode="numeric" placeholder="12345-1234567-1" maxlength="15" class="field" value="{{ old('cnic') }}" @if ($requireCnic) required @endif :aria-invalid="!!errors.cnic">
                                            <p x-show="errors.cnic" x-text="errors.cnic" class="field-error"></p>
                                            @error('cnic')<p class="field-error">{{ $message }}</p>@enderror
                                            <p class="mt-1 text-[11px] text-ink/70">Stored encrypted and only visible to authorised staff.</p>
                                        </div>
                                    @endif
                                    <div>
                                        <label for="b-contact" class="field-label">Preferred contact</label>
                                        <select id="b-contact" name="preferred_contact_method" class="field">
                                            @foreach (\App\Models\Lead::CONTACT_METHODS as $value => $label)
                                                <option value="{{ $value }}" @selected(old('preferred_contact_method', 'phone') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="b-message" class="field-label">Message</label>
                                        <textarea id="b-message" name="message" rows="3" class="field" placeholder="Preferred block, corner plot, visit date…">{{ old('message') }}</textarea>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="rounded-brand border border-accent/40 bg-accent/8 p-4 text-sm text-ink/75">
                                <p class="flex gap-2.5"><x-icon name="shield-check" class="mt-0.5 size-5 text-accent" /> <span>{{ setting('booking_disclaimer') }}</span></p>
                                <label class="mt-4 flex cursor-pointer items-start gap-2.5">
                                    <input type="checkbox" name="agree" value="1" class="mt-0.5 size-4 rounded accent-[var(--brand-accent)]" @checked(old('agree'))>
                                    <span class="font-medium text-primary">I understand this is a booking request and not a confirmed booking or payment.</span>
                                </label>
                                <p x-show="errors.agree" x-text="errors.agree" class="field-error"></p>
                                @error('agree')<p class="field-error">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <p x-show="error" x-text="error" class="mb-3 rounded-brand-sm bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>
                                @error('form')<p class="mb-3 rounded-brand-sm bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                                <button type="submit" class="btn-primary w-full !py-4 text-[15px]" :disabled="loading">
                                    <x-icon name="send" class="size-4" /> <span x-text="loading ? 'Submitting…' : 'Submit Booking Request'">Submit Booking Request</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Live plan summary --}}
                    <aside class="lg:sticky lg:top-24 lg:self-start">
                        <div class="card overflow-hidden">
                            <div class="bg-primary p-6 text-white">
                                <p class="text-xs font-semibold tracking-wider text-accent uppercase">Your Selection</p>
                                <template x-if="plot">
                                    <div>
                                        <p class="mt-2 font-heading text-2xl font-semibold" x-text="plot.size_label"></p>
                                        <p class="text-sm text-white/65" x-text="plan ? plan.name : 'Choose an installment plan'"></p>
                                    </div>
                                </template>
                                <p x-show="!plot" class="mt-2 text-sm text-white/70">Select a plot size to see its payment schedule.</p>
                            </div>
                            <template x-if="plan">
                                <div class="p-6">
                                    <ol class="space-y-3">
                                        <template x-for="(item, index) in plan.items" :key="item.id">
                                            <li class="flex items-start gap-3">
                                                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-primary/8 text-[11px] font-semibold text-primary" x-text="String(index + 1).padStart(2, '0')"></span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-primary" x-text="item.title"></p>
                                                    <p class="text-xs text-ink/70" x-text="item.due_label"></p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold text-primary" x-text="money(item.amount)"></p>
                                                    <p class="text-[11px] text-ink/70" x-text="occurrenceLabel(item)"></p>
                                                </div>
                                            </li>
                                        </template>
                                    </ol>
                                    <div class="mt-5 flex items-center justify-between rounded-brand bg-surface px-4 py-3">
                                        <span class="text-sm font-semibold text-ink/70">Total</span>
                                        <span class="font-heading text-xl font-semibold text-primary" x-text="money(plan.total_price)"></span>
                                    </div>
                                    <p x-show="plot.is_demo_price" class="mt-3 text-center text-[11px] text-ink/70">Demo price — for preview only</p>
                                </div>
                            </template>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2.5">
                            @if (setting('phone'))
                                <a href="{{ tel_url(setting('phone')) }}" class="btn-outline !py-2.5 text-xs"><x-icon name="phone" class="size-4" /> Call Sales</a>
                            @endif
                            @if (whatsapp_url())
                                <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" class="btn-whatsapp !py-2.5 text-xs"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                            @endif
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection
