@if (! empty($video))
    <section id="video" class="relative py-20 sm:py-28">
        <div class="container-x">
            <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" />
            <x-front.video-player :video="$video" class="mx-auto mt-12 max-w-5xl" />
        </div>
    </section>
    @include('front.partials.video-modal')
@endif
