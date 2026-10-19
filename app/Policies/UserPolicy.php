<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users');
    }

    public function create(User $actor): bool
    {
        return $actor->can('users');
    }

    /**
     * Only a Super Admin may edit another Super Admin.
     */
    public function update(User $actor, User $user): bool
    {
        return $actor->can('users') && (! $user->isSuperAdmin() || $actor->isSuperAdmin());
    }

    /**
     * Nobody deletes themselves, and the last active Super Admin can never be removed.
     */
    public function delete(User $actor, User $user): bool
    {
        if (! $this->update($actor, $user) || $actor->is($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return User::query()->where('is_active', true)->whereKeyNot($user->id)
                ->whereHas('role', fn ($query) => $query->where('is_super', true))->exists();
        }

        return true;
    }
}
