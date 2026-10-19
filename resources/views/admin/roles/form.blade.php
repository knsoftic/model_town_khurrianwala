@extends('layouts.admin')

@section('title', $role->exists ? 'Edit '.$role->name : 'New Role')

@section('breadcrumbs')
    <a href="{{ route('admin.roles.index') }}" class="hover:text-slate-900">Roles & Permissions</a> <x-icon name="chevron-right" class="size-3" /> <span>{{ $role->name ?? 'New' }}</span>
@endsection

@php($granted = old('permissions', $role->exists ? $role->permissions->pluck('id')->all() : []))

@section('content')
    <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
        @csrf @if ($role->exists) @method('PUT') @endif
        <x-admin.page-header :title="$role->exists ? $role->name : 'New Role'" />

        <div class="grid gap-6 xl:grid-cols-3">
            <x-admin.card title="Role">
                <div class="space-y-4">
                    <x-admin.input name="name" label="Name" :value="$role->name" required />
                    <x-admin.textarea name="description" label="Description" :value="$role->description" rows="3" />
                </div>
            </x-admin.card>
            <x-admin.card title="Permissions" class="xl:col-span-2">
                @if ($role->is_super)
                    <p class="rounded-lg bg-gold-50 px-4 py-3 text-sm text-gold-700">Super Admin always has every permission, including users and custom scripts.</p>
                @else
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($permissions as $permission)
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 transition hover:border-gold-400 has-[:checked]:border-gold-500 has-[:checked]:bg-gold-50/50">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $granted)) class="mt-0.5 rounded border-slate-300 text-navy-900 focus:ring-gold-500">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">{{ $permission->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $permission->description }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </x-admin.card>
        </div>
        <x-admin.form-actions :cancel="route('admin.roles.index')" />
    </form>
@endsection
