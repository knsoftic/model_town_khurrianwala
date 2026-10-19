@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <x-admin.page-header title="Admin Users" description="Staff who can sign in to the admin panel. What each person can see is set by their role.">
        <x-slot:actions>
            <a href="{{ route('admin.roles.index') }}" class="a-btn-secondary"><x-icon name="shield-check" class="size-4" /> Roles & permissions</a>
            <a href="{{ route('admin.users.create') }}" class="a-btn-gold"><x-icon name="user-plus" class="size-4" /> Add User</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-[1fr_220px_auto]">
        <x-admin.input name="q" :value="request('q')" placeholder="Name or email…" />
        <x-admin.select name="role" :value="request('role')" :options="$roles" placeholder="All roles" />
        <button class="a-btn-primary"><x-icon name="search" class="size-4" /> Filter</button>
    </form>

    <x-admin.card :padding="false">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead><tr><th>User</th><th>Role</th><th>Last sign-in</th><th class="text-center">Active</th><th></th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($user->avatar)
                                        <img src="{{ media_url($user->avatar) }}" alt="" class="size-9 rounded-full object-cover">
                                    @else
                                        <span class="grid size-9 place-items-center rounded-full bg-navy-900 text-xs font-semibold text-gold-400">{{ $user->initials() }}</span>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $user->name }} @if ($user->is(auth()->user()))<span class="text-xs font-normal text-slate-400">(you)</span>@endif</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td><span @class(['a-badge', 'bg-gold-100 text-gold-700 ring-gold-500/30' => $user->role?->is_super, 'bg-slate-100 text-slate-700 ring-slate-300' => ! $user->role?->is_super])>{{ $user->role?->name ?? 'No role' }}</span></td>
                            <td class="text-xs text-slate-500">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="text-center">
                                @if ($user->is(auth()->user()))
                                    <span class="text-xs text-slate-400">—</span>
                                @else
                                    <x-admin.switch :url="route('admin.toggle', ['users', $user->id])" :value="$user->is_active" label="Can sign in" />
                                @endif
                            </td>
                            <td>
                                <x-admin.row-actions :edit="Gate::allows('update', $user) ? route('admin.users.edit', $user) : null" :delete="Gate::allows('delete', $user) ? route('admin.users.destroy', $user) : null" :delete-label="$user->name" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
