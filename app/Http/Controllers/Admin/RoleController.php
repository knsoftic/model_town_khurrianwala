<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->withCount('users')->with('permissions')->orderByDesc('is_super')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Role);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = new Role;
        $this->save($request, $role);

        return redirect()->route('admin.roles.index')->with('success', "Role “{$role->name}” created.");
    }

    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        return $this->form($role->load('permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);
        $this->save($request, $role);

        return redirect()->route('admin.roles.index')->with('success', "Role “{$role->name}” saved.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (! Gate::allows('delete', $role)) {
            return back()->with('error', $role->is_super ? 'The Super Admin role cannot be deleted.' : 'Move its users to another role before deleting it.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', "Role “{$role->name}” deleted.");
    }

    protected function form(Role $role): View
    {
        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => Permission::query()->orderBy('sort_order')->get(),
        ]);
    }

    protected function save(Request $request, Role $role): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'id')],
        ]);

        $role->fill(['name' => $data['name'], 'description' => $data['description'] ?? null]);
        if (! $role->exists) {
            $slug = Str::slug($data['name']) ?: 'role';
            $role->slug = Role::query()->where('slug', $slug)->exists() ? $slug.'-'.Str::lower(Str::random(4)) : $slug;
            $role->is_super = false;
        }
        $role->save();

        if (! $role->is_super) {
            $role->permissions()->sync($data['permissions'] ?? []);
        }
    }
}
