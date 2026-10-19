<?php

namespace App\Http\Controllers\Front;

use App\Enums\PlotStatus;
use App\Http\Requests\Front\BookingRequestForm;
use App\Models\Plot;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends FrontController
{
    public function create(Request $request): View
    {
        $page = $this->page('book-plot');

        $plots = Plot::query()->active()->ordered()
            ->whereIn('status', [PlotStatus::Available, PlotStatus::Limited])
            ->with('activePlans.items')
            ->get();

        $selectedPlot = $plots->firstWhere('slug', $request->query('plot'));
        $selectedPlan = $selectedPlot?->activePlans->firstWhere('slug', $request->query('plan'));

        return view('front.book-plot', [
            'page' => $page,
            'plots' => $plots,
            'selectedPlot' => $selectedPlot,
            'selectedPlan' => $selectedPlan,
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'Book Your Plot']),
        ]);
    }

    public function store(BookingRequestForm $request, LeadService $leads): JsonResponse|RedirectResponse
    {
        $booking = $leads->createBooking($request->safe()->except(['website', 'agree']), $request);

        $message = setting('booking_success_message', 'Your booking request has been received.');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'reference' => $booking->reference]);
        }

        return redirect()->route('book-plot')
            ->with('form_success', $message)
            ->with('booking_reference', $booking->reference);
    }
}
