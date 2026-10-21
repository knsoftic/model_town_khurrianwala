<?php

namespace App\View\Composers;

use App\Enums\BookingStatus;
use App\Enums\LeadStatus;
use App\Models\BookingRequest;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Support\AdminMenu;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $badges = [
            'leads' => $user->can('leads.access') ? Lead::query()->visibleTo($user)->where('status', LeadStatus::New)->count() : 0,
            'bookings' => $user->can('bookings') ? BookingRequest::query()->where('status', BookingStatus::Pending)->count() : 0,
            'messages' => $user->can('leads') ? ContactMessage::query()->whereNull('read_at')->count() : 0,
        ];

        $view->with([
            'adminMenu' => AdminMenu::for($user, $badges),
            'unreadCount' => $user->unreadNotifications()->count(),
            'latestNotifications' => $user->notifications()->latest()->limit(8)->get(),
            'adminLogo' => media_url(setting('logo')),
        ]);
    }
}
