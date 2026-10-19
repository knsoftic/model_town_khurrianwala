@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
    <x-admin.page-header title="My Profile" :description="'Signed in as '.$user->role?->name.($user->last_login_at ? ' · last sign-in '.$user->last_login_at->diffForHumans() : '')" />

    <div class="grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <x-admin.card title="Account details">
                <div class="grid gap-4">
                    <x-admin.input name="name" label="Full name" :value="$user->name" required />
                    <x-admin.input name="email" type="email" label="Email (login)" :value="$user->email" required />
                    <x-admin.input name="phone" label="Phone" :value="$user->phone" />
                    <x-admin.image name="avatar" label="Profile photo" :current="$user->avatar" aspect="aspect-square" />
                </div>
                <div class="mt-6 flex justify-end"><button class="a-btn-primary"><x-icon name="check" class="size-4" /> Save details</button></div>
            </x-admin.card>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}">
            @csrf @method('PUT')
            <x-admin.card title="Change password" description="Use at least 10 characters with upper and lower case letters and a number.">
                <div class="grid gap-4">
                    <x-admin.input name="current_password" type="password" label="Current password" required autocomplete="current-password" />
                    <x-admin.input name="password" type="password" label="New password" required autocomplete="new-password" />
                    <x-admin.input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
                </div>
                <div class="mt-6 flex justify-end"><button class="a-btn-primary"><x-icon name="key-round" class="size-4" /> Change password</button></div>
            </x-admin.card>
        </form>
    </div>
@endsection
