<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users');
    }

    public function create(User $actor): bool
    {
        return $actor->can('users');
    }

    public function update(User $actor, Role $role): bool
    {
        return $actor->can('users') && (! $role->is_super || $actor->isSuperAdmin());
    }

    /**
     * The Super Admin role and roles still assigned to users cannot be deleted.
     */
    public function delete(User $actor, Role $role): bool
    {
        return $actor->can('users') && ! $role->is_super && ! $role->users()->exists();
    }
}
