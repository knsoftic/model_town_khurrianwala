<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NewSubmissionNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Throwable;

class StaffNotifier
{
    public function __construct(protected SettingsService $settings, protected MailSettings $mail) {}

    public function newLead(Lead $lead): void
    {
        $this->safely(function () use ($lead) {
            $lead->loadMissing('plot');

            $this->send('leads', new NewSubmissionNotification(
                kind: 'lead',
                title: $lead->source->label().' received',
                summary: "{$lead->name} ({$lead->phone})".($lead->plot ? " is interested in {$lead->plot->size_label}." : '.'),
                url: route('admin.leads.show', $lead),
                details: [
                    'Name' => $lead->name,
                    'Phone' => $lead->phone,
                    'Email' => $lead->email,
                    'Plot size' => $lead->plot?->size_label,
                    'Budget' => $lead->budget,
                    'Preferred contact' => $lead->contactMethodLabel(),
                    'Preferred time' => $lead->preferred_time,
                    'Message' => $lead->message,
                ],
            ));
        });
    }

    public function newBooking(BookingRequest $booking): void
    {
        $this->safely(function () use ($booking) {
            $booking->loadMissing(['plot', 'installmentPlan']);

            $this->send('bookings', new NewSubmissionNotification(
                kind: 'booking',
                title: 'New booking request '.$booking->reference,
                summary: "{$booking->name} ({$booking->phone}) requested a {$booking->plot?->size_label} plot.",
                url: route('admin.bookings.show', $booking),
                details: [
                    'Reference' => $booking->reference,
                    'Name' => $booking->name,
                    'Phone' => $booking->phone,
                    'Email' => $booking->email,
                    'Plot size' => $booking->plot?->size_label,
                    'Installment plan' => $booking->installmentPlan?->name,
                    'City' => $booking->city,
                    'Message' => $booking->message,
                ],
            ));
        });
    }

    public function newContactMessage(ContactMessage $message): void
    {
        $this->safely(function () use ($message) {
            $this->send('leads', new NewSubmissionNotification(
                kind: 'contact',
                title: 'New contact message',
                summary: "{$message->name}: ".($message->subject ?: str($message->message)->limit(80)),
                url: route('admin.messages.show', $message),
                details: [
                    'Name' => $message->name,
                    'Email' => $message->email,
                    'Phone' => $message->phone,
                    'Subject' => $message->subject,
                    'Message' => $message->message,
                ],
            ));
        });
    }

    /**
     * Notifications must never break a visitor's form submission.
     */
    protected function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            report($e);
        }
    }

    protected function send(string $permission, NewSubmissionNotification $notification): void
    {
        try {
            $recipients = User::query()
                ->where('is_active', true)
                ->whereHas('role', fn (Builder $role) => $role->where('is_super', true)
                    ->orWhereHas('permissions', fn (Builder $query) => $query->where('slug', $permission)))
                ->get();

            Notification::send($recipients, $notification);
        } catch (Throwable $e) {
            report($e);
        }

        $email = $this->settings->get('notification_email');

        if ($this->settings->bool('notify_by_email') && $email) {
            // Sent after the response so visitors never wait on the mail server.
            app()->terminating(function () use ($email, $notification) {
                try {
                    $this->mail->apply();
                    Notification::route('mail', $email)->notifyNow($notification);
                } catch (Throwable $e) {
                    report($e);
                }
            });
        }
    }
}
