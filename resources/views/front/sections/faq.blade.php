@if (($faqs ?? collect())->isNotEmpty())
    <section id="faq" class="relative bg-white py-20 sm:py-28">
        <div class="container-x grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
            <div>
                <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" align="left" />
                <div class="mt-8 rounded-brand-lg bg-surface p-6">
                    <p class="font-semibold text-primary">Still have questions?</p>
                    <p class="mt-1 text-sm text-ink/60">Our sales team is happy to help you choose the right plot and plan.</p>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        <button type="button" @click="$store.callback.show()" class="btn-dark !py-2.5"><x-icon name="phone-call" class="size-4" /> Request Callback</button>
                        @if (whatsapp_url())
                            <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" class="btn-whatsapp !py-2.5"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <x-front.faq-list :faqs="$faqs" />
                @if ($section->setting('button_text'))
                    <a href="{{ url($section->setting('button_url', '/faq')) }}" class="mt-8 inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-accent">{{ $section->setting('button_text') }} <x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
        </div>
    </section>
@endif
