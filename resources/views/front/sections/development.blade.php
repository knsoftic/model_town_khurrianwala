@if (($milestones ?? collect())->isNotEmpty())
    @php($overall = (int) round($milestones->avg('percentage')))
    <section id="development" class="relative py-20 sm:py-28">
        <div class="container-x grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
            <div class="lg:sticky lg:top-28 lg:self-start" x-reveal>
                <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" align="left" />

                <div class="mt-10 flex items-center gap-6 rounded-brand-lg bg-primary p-6 text-white">
                    <div class="relative size-28 shrink-0">
                        <svg viewBox="0 0 120 120" class="size-full -rotate-90">
                            <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="10" class="text-white/10" />
                            <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="10" stroke-linecap="round" class="text-accent"
                                    stroke-dasharray="{{ round(2 * M_PI * 52, 2) }}" stroke-dashoffset="{{ round(2 * M_PI * 52 * (1 - $overall / 100), 2) }}" />
                        </svg>
                        <span class="absolute inset-0 grid place-items-center font-heading text-2xl font-semibold">{{ $overall }}%</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Overall Progress</p>
                        <p class="mt-1 text-sm text-white/65">Average completion across {{ $milestones->count() }} development areas.</p>
                    </div>
                </div>

                @if ($section->setting('button_text'))
                    <a href="{{ url($section->setting('button_url', '/development')) }}" class="btn-outline mt-6">{{ $section->setting('button_text') }} <x-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>

            <div class="space-y-4">
                @foreach ($milestones as $milestone)
                    <x-front.milestone :milestone="$milestone" />
                @endforeach
            </div>
        </div>
    </section>
@endif
