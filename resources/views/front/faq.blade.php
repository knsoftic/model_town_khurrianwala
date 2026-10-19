@extends('layouts.front')

@section('content')
    <x-front.page-banner :page="$page" :crumbs="['FAQ' => null]" />

    <section class="py-16 sm:py-24" x-data="{ category: 'all' }">
        <div class="container-x grid gap-10 lg:grid-cols-[260px_1fr]">
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <p class="mb-3 text-xs font-semibold tracking-wider text-ink/70 uppercase">Categories</p>
                <div class="scrollbar-none -mx-4 flex gap-2 overflow-x-auto px-4 lg:mx-0 lg:flex-col lg:px-0">
                    <button type="button" @click="category = 'all'" :class="category === 'all' ? 'bg-primary text-white' : 'bg-white text-primary ring-1 ring-primary/10'" class="shrink-0 rounded-brand px-4 py-2.5 text-left text-sm font-semibold transition">All Questions</button>
                    @foreach ($groups as $name => $items)
                        <button type="button" @click="category = @js($name)" :class="category === @js($name) ? 'bg-primary text-white' : 'bg-white text-primary ring-1 ring-primary/10'" class="flex shrink-0 items-center justify-between gap-4 rounded-brand px-4 py-2.5 text-left text-sm font-semibold transition">
                            {{ $name }} <span class="text-xs opacity-60">{{ $items->count() }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="mt-8 hidden rounded-brand-lg bg-primary p-6 text-white lg:block">
                    <p class="font-semibold">Didn't find your answer?</p>
                    <p class="mt-1 text-sm text-white/65">Talk to our sales team directly.</p>
                    <button type="button" @click="$store.callback.show()" class="btn-primary mt-5 w-full !py-2.5">Request Callback</button>
                </div>
            </aside>

            <div class="space-y-10">
                @if ($page->content)
                    <div class="prose-brand">{!! $page->content !!}</div>
                @endif
                @forelse ($groups as $name => $items)
                    <div x-show="category === 'all' || category === @js($name)">
                        <h2 class="mb-4 text-2xl font-semibold text-primary">{{ $name }}</h2>
                        <x-front.faq-list :faqs="$items" />
                    </div>
                @empty
                    <p class="rounded-brand-lg bg-white p-12 text-center text-ink/70">FAQs will be added soon.</p>
                @endforelse
            </div>
        </div>
    </section>

    @include('front.partials.cta-band')
@endsection
