@extends('layouts.auth')

@section('title', 'Choose a new password')

@section('content')
    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Choose a new password</h2>

    <form method="POST" action="{{ route('admin.password.update') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-admin.input name="email" type="email" label="Email address" :value="$email" required />
        <x-admin.input name="password" type="password" label="New password" required autocomplete="new-password" />
        <x-admin.input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
        <button type="submit" class="a-btn-primary w-full !py-2.5">Reset password</button>
    </form>
@endsection
