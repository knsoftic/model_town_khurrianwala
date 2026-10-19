@extends('layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')
    <x-admin.page-header title="Roles & Permissions" description="Each role decides which admin sections its users can open. Super Admin always has full access.">
        <x-slot:actions><a href="{{ route('admin.roles.create') }}" class="a-btn-gold"><x-icon name="plus" class="size-4" /> New Role</a></x-slot:actions>
    </x-admin.page-header>

    <x-admin.card :padding="false">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th>Role</th>
                        @foreach ($permissions as $permission)<th class="text-center" title="{{ $permission->description }}">{{ $permission->name }}</th>@endforeach
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        @php($granted = $role->permissions->pluck('slug')->all())
                        <tr>
                            <td>
                                <p class="font-semibold text-slate-900">{{ $role->name }}</p>
                                <p class="text-xs text-slate-500">{{ $role->users_count }} {{ str('user')->plural($role->users_count) }}</p>
                            </td>
                            @foreach ($permissions as $permission)
                                <td class="text-center">
                                    @if ($role->is_super || in_array($permission->slug, $granted, true))
                                        <x-icon name="check" class="mx-auto size-4 text-emerald-600" stroke="2.5" />
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <x-admin.row-actions :edit="Gate::allows('update', $role) ? route('admin.roles.edit', $role) : null" :delete="Gate::allows('delete', $role) ? route('admin.roles.destroy', $role) : null" :delete-label="$role->name" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection
