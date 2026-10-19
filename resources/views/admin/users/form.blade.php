@extends('layouts.admin')

@php($editing = $user->exists)

@section('title', $editing ? 'Edit '.$user->name : 'Add User')

@section('breadcrumbs')
    <a href="{{ route('admin.users.index') }}" class="hover:text-slate-900">Users</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $user->name ?? 'New' }}</span>
@endsection

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" enctype="multipart/form-data">
        @csrf @if ($editing) @method('PUT') @endif
        <x-admin.page-header :title="$editing ? $user->name : 'Add User'" />

        <div class="grid gap-6 xl:grid-cols-3">
            <x-admin.card title="Account" class="xl:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="name" label="Full name" :value="$user->name" required />
                    <x-admin.input name="email" type="email" label="Email (login)" :value="$user->email" required autocomplete="off" />
                    <x-admin.input name="phone" label="Phone" :value="$user->phone" />
                    <div>
                        <label class="a-label" for="f-role_id">Role</label>
                        <select id="f-role_id" name="role_id" class="a-input" @disabled($user->is(auth()->user())) required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((int) old('role_id', $user->role_id) === $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @if ($user->is(auth()->user()))<input type="hidden" name="role_id" value="{{ $user->role_id }}"><p class="a-help">You cannot change your own role.</p>@endif
                        @error('role_id')<p class="a-error">{{ $message }}</p>@enderror
                    </div>
                    <x-admin.input name="password" type="password" :label="$editing ? 'New password (leave blank to keep)' : 'Password'" :required="! $editing" autocomplete="new-password" />
                    <x-admin.input name="password_confirmation" type="password" label="Confirm password" :required="! $editing" autocomplete="new-password" />
                </div>
            </x-admin.card>
            <div class="space-y-6">
                <x-admin.card title="Photo">
                    <x-admin.image name="avatar" :current="$user->avatar" aspect="aspect-square" />
                </x-admin.card>
                @unless ($user->is(auth()->user()))
                    <x-admin.card title="Access">
                        <x-admin.toggle name="is_active" label="Can sign in" :checked="$user->is_active" help="Turn off to block access without deleting the account." />
                    </x-admin.card>
                @endunless
            </div>
        </div>
        <x-admin.form-actions :cancel="route('admin.users.index')" />
    </form>
@endsection
