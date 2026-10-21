@php
    $image = $section->setting('background_image');
    $whatsapp = whatsapp_url();
@endphp

<section id="contact-cta" class="relative isolate overflow-hidden bg-primary py-20 text-white sm:py-28">
    @if ($image)
        <img {{ img_attrs($image, '100vw', [640, 960, 1280, 1920]) }} alt="{{ setting('site_name') }}" loading="lazy" class="absolute inset-0 -z-20 size-full object-cover opacity-30">
    @endif
    <div class="pointer-events-none absolute inset-0 -z-10 bg-grid-light"></div>
    <div class="pointer-events-none absolute -right-40 -bottom-40 -z-10 size-[560px] rounded-full bg-accent/20 blur-3xl"></div>

    <div @class(['container-x grid items-center gap-12', 'lg:grid-cols-2 lg:gap-16' => $section->setting('show_form', true)])>
        <div>
            <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" align="left" :dark="true" />

            <div class="mt-10 grid gap-3 sm:grid-cols-2">
                @if (setting('phone'))
                    <a href="{{ tel_url(setting('phone')) }}" class="flex items-center gap-4 rounded-brand-lg border border-white/10 bg-white/5 p-4 transition hover:border-accent/50 hover:bg-white/10">
                        <span class="grid size-11 place-items-center rounded-full bg-accent text-primary"><x-icon name="phone" class="size-5" /></span>
                        <span><span class="block text-xs text-white/60">Call us</span><span class="block font-semibold">{{ setting('phone') }}</span></span>
                    </a>
                @endif
                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="flex items-center gap-4 rounded-brand-lg border border-white/10 bg-white/5 p-4 transition hover:border-[#25D366]/60 hover:bg-white/10">
                        <span class="grid size-11 place-items-center rounded-full bg-[#25D366] text-white"><x-icon name="whatsapp" class="size-5" /></span>
                        <span><span class="block text-xs text-white/60">WhatsApp</span><span class="block font-semibold">{{ setting('whatsapp_number') }}</span></span>
                    </a>
                @endif
                @if (setting('email'))
                    <a href="mailto:{{ setting('email') }}" class="flex items-center gap-4 rounded-brand-lg border border-white/10 bg-white/5 p-4 transition hover:border-accent/50 hover:bg-white/10">
                        <span class="grid size-11 place-items-center rounded-full bg-white/10 text-accent"><x-icon name="mail" class="size-5" /></span>
                        <span class="min-w-0"><span class="block text-xs text-white/60">Email</span><span class="block truncate font-semibold">{{ setting('email') }}</span></span>
                    </a>
                @endif
                <button type="button" @click="$store.callback.show()" class="flex items-center gap-4 rounded-brand-lg border border-white/10 bg-white/5 p-4 text-left transition hover:border-accent/50 hover:bg-white/10">
                    <span class="grid size-11 place-items-center rounded-full bg-white/10 text-accent"><x-icon name="phone-call" class="size-5" /></span>
                    <span><span class="block text-xs text-white/60">Busy right now?</span><span class="block font-semibold">Request a callback</span></span>
                </button>
            </div>

            @if ($section->setting('primary_button_text'))
                <a href="{{ url($section->setting('primary_button_url', '/book-plot')) }}" class="btn-primary mt-8 !px-7 !py-4">
                    <x-icon name="key-round" class="size-4" /> {{ $section->setting('primary_button_text') }}
                </a>
            @endif
        </div>

        @if ($section->setting('show_form', true))
            <div class="rounded-brand-lg bg-white p-6 text-ink shadow-2xl sm:p-8">
                <x-front.lead-form :plots="$plots ?? collect()" :title="setting('lead_form_title')" :intro="setting('lead_form_text')" />
            </div>
        @endif
    </div>
</section>
