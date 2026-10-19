<section id="calculator" class="relative overflow-hidden bg-primary py-20 text-white sm:py-28">
    <div class="pointer-events-none absolute inset-0 bg-grid-light"></div>
    <div class="pointer-events-none absolute -top-32 -left-32 size-[480px] rounded-full bg-accent/15 blur-3xl"></div>

    <div class="relative container-x">
        <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" :dark="true" />
        <x-front.calculator class="mt-14" :initial="$initialPlot ?? null" />
    </div>
</section>
