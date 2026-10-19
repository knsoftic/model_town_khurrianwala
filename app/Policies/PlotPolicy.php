<?php

namespace App\Policies;

use App\Models\Plot;
use App\Models\User;

class PlotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('plots');
    }

    public function create(User $user): bool
    {
        return $user->can('plots');
    }

    public function update(User $user, Plot $plot): bool
    {
        return $user->can('plots');
    }

    public function delete(User $user, Plot $plot): bool
    {
        return $user->can('plots');
    }
}
