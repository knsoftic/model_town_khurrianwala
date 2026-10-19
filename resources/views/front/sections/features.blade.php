@if (($features ?? collect())->isNotEmpty())
    <section id="features" class="relative bg-white py-20 sm:py-28">
        <div class="container-x">
            <x-front.section-heading :eyebrow="$section->eyebrow" :heading="$section->heading" :subheading="$section->subheading" />

            <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($features as $feature)
                    <x-front.feature-card :feature="$feature" x-reveal />
                @endforeach
            </div>
        </div>
    </section>
@endif
