<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

/**
 * Applies the sender and SMTP details saved under Settings → Email & Notifications
 * to the mailer at runtime (the .env mail settings are used otherwise).
 */
class MailSettings
{
    public function __construct(protected SettingsService $settings) {}

    public function apply(): void
    {
        if ($address = $this->settings->get('mail_from_address')) {
            config(['mail.from.address' => $address]);
        }

        if ($name = $this->settings->get('mail_from_name')) {
            config(['mail.from.name' => $name]);
        }

        if ($this->settings->bool('smtp_enabled') && $this->settings->get('smtp_host')) {
            $encryption = $this->settings->get('smtp_encryption', 'tls');

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $this->settings->get('smtp_host'),
                'mail.mailers.smtp.port' => (int) $this->settings->get('smtp_port', 587),
                'mail.mailers.smtp.username' => $this->settings->get('smtp_username'),
                'mail.mailers.smtp.password' => $this->settings->secret('smtp_password'),
                'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : null,
            ]);
        }

        Mail::purge();
    }
}
