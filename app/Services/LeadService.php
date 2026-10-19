<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\BookingRequest;
use App\Models\ContactMessage;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns public form submissions into leads / booking requests and alerts staff.
 */
class LeadService
{
    public function __construct(protected StaffNotifier $notifier) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createLead(array $data, LeadSource $source, Request $request): Lead
    {
        $lead = Lead::query()->create(array_merge($data, $this->requestMeta($request), [
            'source' => $source->value,
            'status' => LeadStatus::New->value,
            'page_url' => Str::limit((string) ($data['page_url'] ?? $request->headers->get('referer')), 490, ''),
        ]));

        $this->notifier->newLead($lead);

        return $lead;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createBooking(array $data, Request $request): BookingRequest
    {
        $booking = DB::transaction(function () use ($data, $request) {
            $lead = Lead::query()->create(array_merge($this->requestMeta($request), [
                'plot_id' => $data['plot_id'],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'preferred_contact_method' => $data['preferred_contact_method'] ?? null,
                'payment_preference' => 'installment',
                'message' => $data['message'] ?? null,
                'source' => LeadSource::Booking->value,
                'status' => LeadStatus::New->value,
            ]));

            $booking = BookingRequest::query()->create(array_merge($this->requestMeta($request), [
                'reference' => 'TMP-'.Str::upper(Str::random(12)),
                'plot_id' => $data['plot_id'],
                'installment_plan_id' => $data['installment_plan_id'] ?? null,
                'lead_id' => $lead->id,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'cnic' => $data['cnic'] ?? null,
                'city' => $data['city'] ?? null,
                'preferred_contact_method' => $data['preferred_contact_method'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => BookingStatus::Pending->value,
            ]));

            $booking->update(['reference' => sprintf('BK-%s-%04d', now()->format('ym'), $booking->id)]);

            return $booking;
        });

        $this->notifier->newBooking($booking);

        return $booking;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createContactMessage(array $data, Request $request): ContactMessage
    {
        $message = ContactMessage::query()->create(array_merge($data, $this->requestMeta($request)));

        $this->notifier->newContactMessage($message);

        return $message;
    }

    /**
     * @return array{ip_address: string|null, user_agent: string}
     */
    protected function requestMeta(Request $request): array
    {
        return [
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
        ];
    }
}
