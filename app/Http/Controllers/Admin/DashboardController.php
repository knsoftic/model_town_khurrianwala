<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\Lead;
use App\Services\DashboardService;
use App\Support\AdminMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): View|RedirectResponse
    {
        $user = $request->user();

        // Staff without dashboard access land on the first section they can use.
        if (! $user->can('dashboard')) {
            $first = collect(AdminMenu::for($user))->flatMap(fn ($group) => $group['items'])->first();

            return $first ? redirect($first['url']) : redirect()->route('admin.profile.edit');
        }

        return view('admin.dashboard', [
            'stats' => $dashboard->stats(),
            'charts' => $dashboard->charts(),
            'recentLeads' => $user->can('leads')
                ? Lead::query()->with(['plot', 'assignee'])->latest()->limit(6)->get()
                : collect(),
            'pendingBookings' => $user->can('bookings')
                ? BookingRequest::query()->with('plot')->where('status', BookingStatus::Pending)->latest()->limit(5)->get()
                : collect(),
            'followUps' => $user->can('leads')
                ? Lead::query()->with('plot')->whereNotNull('follow_up_date')->whereDate('follow_up_date', '<=', today()->addDays(2))
                    ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Cancelled])->orderBy('follow_up_date')->limit(6)->get()
                : collect(),
        ]);
    }
}
