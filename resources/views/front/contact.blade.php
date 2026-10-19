@extends('layouts.front')

@php
    $mapEmbed = \App\Support\MapEmbed::extract(setting('google_maps_embed')) ?? $location?->mapEmbedSrc();
    $whatsapp = whatsapp_url();
    $cards = array_filter([
        setting('phone') ? ['phone', 'Call Us', setting('phone'), tel_url(setting('phone')), setting('phone_secondary')] : null,
        $whatsapp ? ['whatsapp', 'WhatsApp', setting('whatsapp_number'), $whatsapp, 'Quick replies during office hours'] : null,
        setting('email') ? ['mail', 'Email', setting('email'), 'mailto:'.setting('email'), setting('email_secondary')] : null,
        setting('office_address') ? ['map-pin', 'Visit Our Office', setting('office_address'), setting('google_maps_url'), setting('office_hours')] : null,
    ]);
@endphp

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['Contact' => null]" />

    <section class="relative -mt-10 pb-10">
        <div class="container-x grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($cards as [$icon, $title, $value, $href, $extra])
                <a @if ($href) href="{{ $href }}" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif @endif
                   class="group rounded-brand-lg border border-primary/8 bg-white p-6 shadow-soft transition hover:-translate-y-0.5 hover:shadow-lift">
                    <span class="grid size-12 place-items-center rounded-full bg-accent/15 text-accent transition group-hover:bg-accent group-hover:text-primary"><x-icon :name="$icon" class="size-5" /></span>
                    <p class="mt-4 text-xs font-semibold tracking-wider text-ink/50 uppercase">{{ $title }}</p>
                    <p class="mt-1 font-semibold break-words text-primary">{{ $value }}</p>
                    @if ($extra)
                        <p class="mt-1 text-sm text-ink/55">{{ $extra }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </section>

    <section class="pb-16 sm:pb-24">
        <div class="container-x grid gap-8 lg:grid-cols-2">
            <div class="card p-6 sm:p-8">
                <h2 class="text-2xl font-semibold text-primary">Send Us a Message</h2>
                @if ($page->content)
                    <div class="prose-brand mt-2 text-sm">{!! $page->content !!}</div>
                @endif

                @if (session('form_success'))
                    <div class="mt-5 rounded-brand bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('form_success') }}</div>
                @endif

                <form x-data="ajaxForm" @submit.prevent="submit" action="{{ route('contact.store') }}" method="POST" class="mt-6" novalidate>
                    @csrf
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                    <div x-cloak x-show="success" class="rounded-brand bg-emerald-50 p-6 text-center">
                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-emerald-500 text-white"><x-icon name="check" class="size-6" stroke="2.5" /></span>
                        <p class="mt-3 font-medium text-emerald-900" x-text="success"></p>
                        <button type="button" @click="reset()" class="mt-4 text-sm font-semibold text-emerald-800 underline underline-offset-4">Send another message</button>
                    </div>

                    <div x-show="!success" class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="c-name" class="field-label">Full name <span class="text-rose-500">*</span></label>
                            <input id="c-name" name="name" type="text" required autocomplete="name" class="field" value="{{ old('name') }}" :aria-invalid="!!errors.name">
                            <p x-show="errors.name" x-text="errors.name" class="field-error"></p>
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="c-email" class="field-label">Email</label>
                            <input id="c-email" name="email" type="email" autocomplete="email" class="field" value="{{ old('email') }}" :aria-invalid="!!errors.email">
                            <p x-show="errors.email" x-text="errors.email" class="field-error"></p>
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="c-phone" class="field-label">Phone</label>
                            <input id="c-phone" name="phone" type="tel" autocomplete="tel" placeholder="+92 3XX XXXXXXX" class="field" value="{{ old('phone') }}" :aria-invalid="!!errors.phone">
                            <p x-show="errors.phone" x-text="errors.phone" class="field-error"></p>
                            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="c-subject" class="field-label">Subject</label>
                            <input id="c-subject" name="subject" type="text" class="field" value="{{ old('subject') }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="c-message" class="field-label">Message <span class="text-rose-500">*</span></label>
                            <textarea id="c-message" name="message" rows="5" required class="field" :aria-invalid="!!errors.message">{{ old('message') }}</textarea>
                            <p x-show="errors.message" x-text="errors.message" class="field-error"></p>
                            @error('message')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <p x-show="error" x-text="error" class="mb-3 rounded-brand-sm bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>
                            @error('form')<p class="mb-3 rounded-brand-sm bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            <button type="submit" class="btn-primary w-full !py-3.5 sm:w-auto sm:!px-8" :disabled="loading">
                                <x-icon name="send" class="size-4" /> <span x-text="loading ? 'Sending…' : 'Send Message'">Send Message</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="flex flex-col gap-6">
                @if ($page->setting('show_map', true) && $mapEmbed)
                    <div class="min-h-80 flex-1 overflow-hidden rounded-brand-lg shadow-soft ring-1 ring-primary/8">
                        <iframe src="{{ $mapEmbed }}" title="Office location" class="size-full min-h-80" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                @endif
                @if ($page->setting('show_callback', true))
                    <div class="rounded-brand-lg bg-primary p-6 text-white sm:p-8">
                        <h2 class="text-2xl font-semibold">Prefer a phone call?</h2>
                        <p class="mt-1.5 text-sm text-white/70">Leave your number and our sales team will call you back.</p>
                        <button type="button" @click="$store.callback.show()" class="btn-primary mt-5"><x-icon name="phone-call" class="size-4" /> Request a Callback</button>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="bg-white py-16 sm:py-24">
        <div class="container-x grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
            <x-front.section-heading eyebrow="Plot Inquiry" :heading="setting('lead_form_title')" :subheading="setting('lead_form_text')" align="left" />
            <div class="card p-6 sm:p-8">
                <x-front.lead-form :plots="$plots" />
            </div>
        </div>
    </section>
@endsection
