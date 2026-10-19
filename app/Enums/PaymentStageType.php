<?php

namespace App\Enums;

enum PaymentStageType: string
{
    case Booking = 'booking';
    case Advance = 'advance';
    case FullPayment = 'full_payment';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case HalfYearly = 'half_yearly';
    case After6Months = 'after_6_months';
    case After12Months = 'after_12_months';
    case After18Months = 'after_18_months';
    case After24Months = 'after_24_months';
    case EndPayment = 'end_payment';
    case Registry = 'registry';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Booking => 'Booking',
            self::Advance => 'Advance / Down Payment',
            self::FullPayment => 'Full Payment',
            self::Monthly => 'Monthly Installment',
            self::Quarterly => 'Quarterly Payment',
            self::HalfYearly => '6-Month Installment',
            self::After6Months => 'After 6 Months',
            self::After12Months => 'After 12 Months',
            self::After18Months => 'After 18 Months',
            self::After24Months => 'After 24 Months',
            self::EndPayment => 'End Payment',
            self::Registry => 'On Registry',
            self::Custom => 'Custom Payment',
        };
    }

    /**
     * Whether this stage repeats on an interval (and so counts as an installment).
     */
    public function isRecurring(): bool
    {
        return in_array($this, [self::Monthly, self::Quarterly, self::HalfYearly], true);
    }

    /**
     * Months between each occurrence of a recurring stage.
     */
    public function intervalMonths(): int
    {
        return match ($this) {
            self::Quarterly => 3,
            self::HalfYearly => 6,
            default => 1,
        };
    }

    /**
     * Sensible default for "due after N months" when a stage type is picked.
     */
    public function defaultDueAfterMonths(): ?int
    {
        return match ($this) {
            self::Booking, self::FullPayment => 0,
            self::Advance => 0,
            self::Monthly, self::Quarterly => 1,
            self::HalfYearly => 6,
            self::After6Months => 6,
            self::After12Months => 12,
            self::After18Months => 18,
            self::After24Months => 24,
            default => null,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Booking => 'bookmark-check',
            self::Advance, self::FullPayment => 'wallet',
            self::Monthly => 'calendar-days',
            self::Quarterly => 'calendar-range',
            self::HalfYearly => 'calendar-sync',
            self::After6Months, self::After12Months, self::After18Months, self::After24Months => 'calendar-clock',
            self::EndPayment => 'key-round',
            self::Registry => 'stamp',
            self::Custom => 'receipt',
        };
    }

    /**
     * How a repeating stage reads next to its amount: "× 24 months", "× 4 (every 6 months)".
     */
    public function repeatText(int $occurrences): string
    {
        return match ($this) {
            self::Quarterly => '× '.$occurrences.' '.str('quarter')->plural($occurrences),
            self::HalfYearly => '× '.$occurrences.' (every 6 months)',
            default => '× '.$occurrences.' '.str('month')->plural($occurrences),
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
