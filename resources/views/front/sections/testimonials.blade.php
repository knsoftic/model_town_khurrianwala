@if (($testimonials ?? collect())->isNotEmpty())
    <section id="testimonials" class="relative overflow-hidden bg-white py-20 sm:py-28" x-data="carousel">
        <div class="container-x">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" align="left" />
                <div class="flex shrink-0 gap-2">
                    <button type="button" @click="scroll(-1)" :disabled="atStart" class="grid size-12 place-items-center rounded-full border border-primary/15 text-primary transition hover:bg-primary hover:text-white disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-primary" aria-label="Previous testimonials">
                        <x-icon name="arrow-left" class="size-5" />
                    </button>
                    <button type="button" @click="scroll(1)" :disabled="atEnd" class="grid size-12 place-items-center rounded-full border border-primary/15 text-primary transition hover:bg-primary hover:text-white disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-primary" aria-label="Next testimonials">
                        <x-icon name="arrow-right" class="size-5" />
                    </button>
                </div>
            </div>

            <div x-ref="track" @scroll.debounce.50ms="update()"
                 class="scrollbar-none -mx-4 mt-12 flex snap-x snap-mandatory scroll-px-4 gap-6 overflow-x-auto scroll-smooth px-4 pb-4 sm:-mx-6 sm:scroll-px-6 sm:px-6 lg:-mx-8 lg:scroll-px-8 lg:px-8">
                @foreach ($testimonials as $testimonial)
                    <x-front.testimonial-card :testimonial="$testimonial" class="w-[85%] shrink-0 snap-start sm:w-[420px]" />
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('testimonials') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-accent">Read all reviews <x-icon name="arrow-right" class="size-4" /></a>
            </div>
        </div>
    </section>
    @include('front.partials.video-modal')
@endif
