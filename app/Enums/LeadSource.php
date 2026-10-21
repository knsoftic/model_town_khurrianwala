<?php

namespace App\Enums;

enum LeadSource: string
{
    case Inquiry = 'inquiry';
    case PlotPage = 'plot_page';
    case Callback = 'callback';
    case Booking = 'booking';
    case Contact = 'contact';
    case Calculator = 'calculator';
    case WhatsApp = 'whatsapp';
    case Phone = 'phone';
    case Facebook = 'facebook';
    case WalkIn = 'walk_in';
    case Referral = 'referral';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Inquiry => 'Website Inquiry',
            self::PlotPage => 'Plot Page',
            self::Callback => 'Callback Request',
            self::Booking => 'Booking Request',
            self::Contact => 'Contact Page',
            self::Calculator => 'Payment Calculator',
            self::WhatsApp => 'WhatsApp',
            self::Phone => 'Phone Call',
            self::Facebook => 'Facebook Ad',
            self::WalkIn => 'Walk-in',
            self::Referral => 'Referral',
            self::Other => 'Other',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
