@extends('layouts.front')

@section('content')
    <section class="relative overflow-hidden bg-primary py-28 text-white sm:py-36">
        <div class="pointer-events-none absolute inset-0 bg-grid-light"></div>
        <div class="relative container-x text-center">
            <p class="font-heading text-7xl font-semibold text-accent sm:text-8xl">404</p>
            <h1 class="mt-4 text-3xl font-semibold sm:text-4xl">This page could not be found</h1>
            <p class="mx-auto mt-4 max-w-xl text-white/70">The page may have moved, or the plot or location is no longer listed.</p>
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="btn-primary">Back to Home</a>
                <a href="{{ route('plots.index') }}" class="btn-ghost-light">View Plot Sizes</a>
            </div>
        </div>
    </section>
@endsection
