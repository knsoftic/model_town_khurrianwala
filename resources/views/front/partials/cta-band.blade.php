{{-- Compact conversion band used at the bottom of inner pages. --}}
<section class="py-16 sm:py-20">
    <div class="container-x">
        <div class="relative overflow-hidden rounded-brand-lg bg-primary px-6 py-12 text-white sm:px-12">
            <div class="pointer-events-none absolute inset-0 bg-grid-light"></div>
            <div class="pointer-events-none absolute -top-24 -right-24 size-80 rounded-full bg-accent/20 blur-3xl"></div>
            <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-semibold sm:text-4xl">{{ setting('lead_form_title', 'Get Prices & Payment Plan') }}</h2>
                    <p class="mt-3 text-white/70">{{ setting('lead_form_text') }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if (setting('header_cta_primary_text'))
                        <a href="{{ url(setting('header_cta_primary_url', '/book-plot')) }}" class="btn-primary !px-6 !py-3.5"><x-icon name="key-round" class="size-4" /> {{ setting('header_cta_primary_text') }}</a>
                    @endif
                    <button type="button" @click="$store.callback.show()" class="btn-ghost-light !px-6 !py-3.5"><x-icon name="phone-call" class="size-4" /> Request Callback</button>
                    @if (whatsapp_url())
                        <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" class="btn-whatsapp !px-6 !py-3.5"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
