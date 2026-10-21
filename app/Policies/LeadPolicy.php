<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

/**
 * Staff with "leads" work on every lead; agents ("my_leads") on the leads they added
 * or that are assigned to them. Deleting and bulk changes need "leads".
 */
class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leads.access');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $lead->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->can('leads.access');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $lead->isVisibleTo($user);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('leads');
    }
}
