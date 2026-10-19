<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerts staff about a new lead, booking request or contact message:
 * stored in the admin panel (database) and optionally emailed.
 */
class NewSubmissionNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, string|null>  $details
     */
    public function __construct(
        public string $kind,
        public string $title,
        public string $summary,
        public string $url,
        public array $details = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title.' — '.setting('site_name'))
            ->greeting($this->title)
            ->line($this->summary);

        foreach (array_filter($this->details, fn ($value) => filled($value)) as $label => $value) {
            $mail->line("**{$label}:** {$value}");
        }

        return $mail->action('Open in admin panel', $this->url);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'title' => $this->title,
            'summary' => $this->summary,
            'url' => $this->url,
        ];
    }
}
