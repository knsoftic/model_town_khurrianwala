@extends('layouts.auth')

@section('title', 'Reset password')

@section('content')
    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Forgot your password?</h2>
    <p class="mt-1 text-sm text-slate-500">Enter your email and we'll send you a reset link.</p>

    @if (session('status'))
        <div class="mt-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.password.email') }}" class="mt-8 space-y-5">
        @csrf
        <x-admin.input name="email" type="email" label="Email address" required autofocus />
        <button type="submit" class="a-btn-primary w-full !py-2.5">Email reset link</button>
    </form>

    <a href="{{ route('admin.login') }}" class="mt-8 inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-900"><x-icon name="arrow-left" class="size-4" /> Back to sign in</a>
@endsection
