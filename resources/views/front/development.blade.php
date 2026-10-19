@extends('layouts.front')

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['Development' => null]" />

    <section class="py-16 sm:py-24">
        <div class="container-x">
            @if ($page->content)
                <div class="prose-brand mx-auto mb-12 max-w-3xl text-center">{!! $page->content !!}</div>
            @endif

            @if ($milestones->isEmpty())
                <p class="rounded-brand-lg bg-white p-12 text-center text-ink/70">Development updates will be published soon.</p>
            @else
                <div class="grid gap-10 lg:grid-cols-[340px_1fr]">
                    <aside class="lg:sticky lg:top-24 lg:self-start">
                        <div class="rounded-brand-lg bg-primary p-8 text-center text-white">
                            <div class="relative mx-auto size-44">
                                <svg viewBox="0 0 120 120" class="size-full -rotate-90">
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="9" class="text-white/10" />
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="currentColor" stroke-width="9" stroke-linecap="round" class="text-accent"
                                            stroke-dasharray="{{ round(2 * M_PI * 52, 2) }}" stroke-dashoffset="{{ round(2 * M_PI * 52 * (1 - $overall / 100), 2) }}" />
                                </svg>
                                <span class="absolute inset-0 grid place-items-center"><span><span class="block font-heading text-4xl font-semibold">{{ $overall }}%</span><span class="text-xs text-white/60">Overall</span></span></span>
                            </div>
                            <p class="mt-6 text-sm text-white/70">Average progress across {{ $milestones->count() }} development areas.</p>
                            <dl class="mt-6 grid grid-cols-3 gap-2 text-center">
                                @foreach (\App\Enums\MilestoneStatus::cases() as $status)
                                    <div class="rounded-brand-sm bg-white/5 p-2.5"><dd class="font-heading text-xl font-semibold">{{ $milestones->where('status', $status)->count() }}</dd><dt class="text-[11px] text-white/60">{{ $status->label() }}</dt></div>
                                @endforeach
                            </dl>
                        </div>
                    </aside>
                    <div class="space-y-4">
                        @foreach ($milestones as $milestone)
                            <x-front.milestone :milestone="$milestone" :detailed="true" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if ($album && $album->activeImages->isNotEmpty())
        <section data-defer class="bg-white py-16 sm:py-24">
            <div class="container-x">
                <x-front.section-heading eyebrow="On-site Photos" heading="Development in Pictures" />
                <div class="mt-12">
                    <x-front.gallery-grid :images="$album->activeImages->each->setRelation('album', $album)" />
                </div>
                <div class="mt-10 text-center"><a href="{{ route('gallery') }}" class="btn-outline">View Full Gallery <x-icon name="arrow-right" class="size-4" /></a></div>
            </div>
        </section>
    @endif

    @include('front.partials.cta-band')
@endsection
