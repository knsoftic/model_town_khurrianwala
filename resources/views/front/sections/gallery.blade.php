@if (($galleryImages ?? collect())->isNotEmpty())
    <section id="gallery" class="relative py-20 sm:py-28">
        <div class="container-x">
            <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" />
            <div class="mt-12">
                <x-front.gallery-grid :images="$galleryImages" />
            </div>
            @if ($section->setting('button_text'))
                <div class="mt-12 text-center">
                    <a href="{{ url($section->setting('button_url', '/gallery')) }}" class="btn-outline !px-7">{{ $section->setting('button_text') }} <x-icon name="arrow-right" class="size-4" /></a>
                </div>
            @endif
        </div>
    </section>
@endif
