<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\Plot;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BookingRequestController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.bookings.index', [
            'bookings' => $this->filtered($request)->with(['plot', 'installmentPlan', 'assignee'])->latest()->paginate(25)->withQueryString(),
            'counts' => BookingRequest::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'plots' => Plot::withTrashed()->orderBy('sort_order')->pluck('size_label', 'id'),
        ]);
    }

    public function show(BookingRequest $booking): View
    {
        $booking->load(['plot', 'installmentPlan.items', 'lead', 'assignee', 'reviewer', 'notes.user']);

        return view('admin.bookings.show', [
            'booking' => $booking,
            'staff' => User::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Approve / reject / hold / mark contacted, and (re)assign. Keeps the linked lead in step.
     */
    public function update(Request $request, BookingRequest $booking): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(BookingStatus::class)],
            'assigned_to' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        if (isset($data['status']) && $data['status'] !== $booking->status->value) {
            $data['reviewed_by'] = $request->user()->id;
            $data['reviewed_at'] = now();
        }

        $booking->update($data);
        $booking->loadMissing('lead');

        if ($booking->lead && isset($data['status'])) {
            $leadStatus = match ($booking->status) {
                BookingStatus::Approved => LeadStatus::Booked,
                BookingStatus::Rejected => LeadStatus::Cancelled,
                BookingStatus::Contacted, BookingStatus::OnHold => $booking->lead->status === LeadStatus::New ? LeadStatus::Contacted : null,
                default => null,
            };
            if ($leadStatus) {
                $booking->lead->update(['status' => $leadStatus]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Booking request '.strtolower($booking->status->label())]);
        }

        return back()->with('success', "Booking {$booking->reference}: {$booking->status->label()}.");
    }

    public function destroy(BookingRequest $booking): RedirectResponse
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('success', "Booking request {$booking->reference} deleted.");
    }

    public function export(Request $request, string $format, ExportService $export): Response
    {
        $bookings = $this->filtered($request)->with(['plot', 'installmentPlan', 'assignee'])->latest()->limit(20000)->get();

        return $export->download($format, 'booking-requests', [
            'Reference', 'Name', 'Phone', 'Email', 'CNIC', 'City', 'Plot size', 'Installment plan', 'Contact by', 'Message', 'Status', 'Assigned to', 'Received',
        ], $bookings->map(fn (BookingRequest $booking) => [
            $booking->reference, $booking->name, $booking->phone, $booking->email, $booking->cnic, $booking->city,
            $booking->plot?->size_label, $booking->installmentPlan?->name, $booking->preferred_contact_method, $booking->message,
            $booking->status->label(), $booking->assignee?->name, $booking->created_at->format('Y-m-d H:i'),
        ])->all(), 'Booking Requests');
    }

    protected function filtered(Request $request): Builder
    {
        return BookingRequest::query()
            ->search($request->query('q'))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('plot'), fn ($query, $plot) => $query->where('plot_id', $plot))
            ->when($request->query('from'), fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($query, $to) => $query->whereDate('created_at', '<=', $to));
    }
}
