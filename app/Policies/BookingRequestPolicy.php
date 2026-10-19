<?php

namespace App\Policies;

use App\Models\BookingRequest;
use App\Models\User;

class BookingRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('bookings');
    }

    public function view(User $user, BookingRequest $booking): bool
    {
        return $user->can('bookings');
    }

    public function update(User $user, BookingRequest $booking): bool
    {
        return $user->can('bookings');
    }

    public function delete(User $user, BookingRequest $booking): bool
    {
        return $user->can('bookings');
    }
}
