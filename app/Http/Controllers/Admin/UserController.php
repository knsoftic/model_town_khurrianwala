<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesMedia;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    use HandlesMedia;

    public function index(Request $request): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with('role')
                ->when($request->query('q'), fn ($query, $term) => $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")))
                ->when($request->query('role'), fn ($query, $role) => $query->where('role_id', $role))
                ->orderBy('name')->paginate(25)->withQueryString(),
            'roles' => Role::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return $this->form(new User(['is_active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = new User;
        $this->save($request, $user);

        return redirect()->route('admin.users.index')->with('success', "{$user->name} can now sign in to the admin panel.");
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return $this->form($user);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);
        $this->save($request, $user);

        return redirect()->route('admin.users.index')->with('success', "{$user->name} saved.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if (! Gate::allows('delete', $user)) {
            return back()->with('error', $user->is(auth()->user())
                ? 'You cannot delete your own account.'
                : 'This user cannot be deleted (the last active Super Admin must remain).');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "{$user->name} removed.");
    }

    protected function form(User $user): View
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.form', [
            'user' => $user,
            'roles' => auth()->user()->isSuperAdmin() ? $roles : $roles->reject->is_super,
        ]);
    }

    protected function save(Request $request, User $user): void
    {
        $actor = $request->user();
        $allowedRoles = Role::query()->when(! $actor->isSuperAdmin(), fn ($query) => $query->where('is_super', false))->pluck('id');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role_id' => ['required', Rule::in($allowedRoles->all())],
            'is_active' => ['boolean'],
            'password' => [$user->exists ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($user->is($actor)) {
            $data['is_active'] = true;
            $data['role_id'] = $user->role_id;
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $data['avatar'] = $this->media($request, 'avatar', $user->avatar, 'avatars');
        $user->fill($data)->save();
    }
}
