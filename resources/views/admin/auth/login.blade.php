@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Welcome back</h2>
    <p class="mt-1 text-sm text-slate-500">Sign in to the admin panel.</p>

    @if (session('status'))
        <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
        @csrf
        <x-admin.input name="email" type="email" label="Email address" required autofocus autocomplete="username" />
        <div>
            <div class="flex items-center justify-between">
                <label for="f-password" class="a-label">Password</label>
                <a href="{{ route('admin.password.request') }}" class="mb-1.5 text-xs font-semibold text-gold-700 hover:underline">Forgot password?</a>
            </div>
            <x-admin.input name="password" type="password" required autocomplete="current-password" />
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-navy-900 focus:ring-gold-500"> Keep me signed in
        </label>
        <button type="submit" class="a-btn-primary w-full !py-2.5">Sign in <x-icon name="arrow-right" class="size-4" /></button>
    </form>

    <a href="{{ route('home') }}" class="mt-8 inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900"><x-icon name="arrow-left" class="size-4" /> Back to website</a>
@endsection
