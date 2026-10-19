@extends('layouts.front')

@section('content')
    <div class="home-flow">
        @foreach ($sections as $section)
            @includeIf('front.sections.'.$section->type, $data + ['section' => $section, 'first' => $loop->first])
        @endforeach
    </div>
    @if ($sections->isEmpty())
        <section class="container-x py-32 text-center">
            <h1 class="text-4xl font-semibold text-primary">{{ setting('site_name') }}</h1>
            <p class="mt-4 text-ink/70">{{ setting('site_tagline') }}</p>
        </section>
    @endif
@endsection
