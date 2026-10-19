<?php

namespace Database\Seeders;

use App\Enums\PaymentStageType;
use App\Enums\PlotStatus;
use App\Models\Faq;
use App\Models\InstallmentPlan;
use App\Models\Location;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Plot;
use App\Services\SettingsService;
use Database\Seeders\Support\DemoArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Plots and installment plans from the official "Residential Payment Plan" flyer:
 * 3, 5 and 10 Marla residential. Every other plot size and plan is moved to the
 * trash (leads and bookings that mention them are kept), so the website shows only
 * these. New sizes and plans can still be added from the admin panel afterwards.
 *
 *   php artisan db:seed --class=PaymentPlanSeeder --force
 *
 * The flyer lists one "After 6 Month" amount and a monthly amount. The totals only
 * balance with 24 monthly installments plus that amount every 6 months (months 6,
 * 12, 18 and 24), so each plan is 15% booking + 15% confirmation + 24 × monthly +
 * 4 × six-monthly + 25% on possession = 100%.
 */
class PaymentPlanSeeder extends Seeder
{
    public const MONTHS = 24;

    /**
     * size => [total, booking, confirmation, every 6 months, monthly, possession]
     *
     * @var array<string, array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int}>
     */
    public const PLANS = [
        '3 Marla' => [2700000, 405000, 405000, 135000, 28125, 675000],
        '5 Marla' => [4500000, 675000, 675000, 225000, 46875, 1125000],
        '10 Marla' => [9000000, 1350000, 1350000, 450000, 93750, 2250000],
    ];

    public const CASH_DISCOUNT = 0.10;

    public const TERMS = [
        '10% discount on cash payment.',
        'Possession for construction on payment of 30%.',
        '10% extra will be charged on category plots.',
    ];

    public function run(SettingsService $settings): void
    {
        DB::transaction(function () {
            $keep = [];
            $plans = [];

            foreach (array_keys(self::PLANS) as $i => $size) {
                [$plot, $plan] = $this->plot($size, $i);
                $keep[] = $plot->id;
                $plans[] = $plan->id;
            }

            // Only the flyer's sizes and plans remain on the website.
            Plot::query()->whereNotIn('id', $keep)->get()->each->delete();
            InstallmentPlan::query()->whereNotIn('id', $plans)->get()->each->delete();

            NavigationItem::query()->where('url', 'like', '/plots/%')
                ->whereNotIn('url', Plot::query()->pluck('slug')->map(fn (string $slug) => '/plots/'.$slug)->all())
                ->update(['is_active' => false]);

            $this->pages();
            $this->faqs();
        });

        $settings->update('general', [
            'demo_notice_text' => 'Testimonials and photos shown are sample content. Call 0300-8653838 for more information.',
        ]);
    }

    /**
     * @return array{0: Plot, 1: InstallmentPlan}
     */
    protected function plot(string $size, int $order): array
    {
        [$total, $booking, $confirmation, $sixMonthly, $monthly, $possession] = self::PLANS[$size];
        $slug = str($size)->slug()->toString();
        $cash = (int) round($total * (1 - self::CASH_DISCOUNT));
        $money = fn (int $amount) => 'PKR '.number_format($amount);

        $plot = Plot::withTrashed()->firstOrNew(['slug' => $slug]);
        if ($plot->trashed()) {
            $plot->restore();
        }

        $plot->fill([
            'size_label' => $size,
            'title' => "{$size} Residential Plot",
            'category' => 'residential',
            'short_description' => "Total price {$money($total)} — booking {$money($booking)} and {$money($monthly)} a month for ".self::MONTHS.' months.',
            'full_description' => "<p>{$size} residential plot in Model Town Khurrianwala, Faisalabad, on an easy installment plan. Total price {$money($total)}.</p>"
                .'<ul>'
                ."<li>Booking: {$money($booking)}</li>"
                ."<li>Confirmation: {$money($confirmation)}</li>"
                ."<li>Monthly installment: {$money($monthly)} × ".self::MONTHS.'</li>'
                ."<li>After every 6 months: {$money($sixMonthly)} × 4</li>"
                ."<li>Possession: {$money($possession)}</li>"
                .'</ul>'
                .'<h3>Terms and conditions</h3><ul>'.collect(self::TERMS)->map(fn (string $term) => '<li>'.rtrim($term, '.').'</li>')->implode('').'</ul>',
            'dimensions' => null,
            'area_sqft' => null,
            'starting_price' => null,
            'total_price' => $total,
            'booking_amount' => $booking,
            'down_payment' => $confirmation,
            'installment_count' => self::MONTHS,
            'installment_amount' => $monthly,
            'payment_frequency' => 'monthly',
            'after_6_months_payment' => $sixMonthly,
            'after_12_months_payment' => null,
            'end_payment' => $possession,
            'development_charges' => null,
            'possession_charges' => null,
            'total_units' => 0,
            'sold_units' => 0,
            'status' => PlotStatus::Available,
            'is_featured' => true,
            'is_active' => true,
            'is_demo_price' => false,
            'price_note' => '10% discount on cash payment · 10% extra on category plots',
            'sort_order' => $order,
        ]);
        $plot->location_id ??= Location::query()->where('slug', 'khurrianwala-main-site')->value('id');

        // The demo illustration showed made-up dimensions; redraw it without them.
        // An uploaded photo is never replaced.
        if (! $plot->image || str_starts_with($plot->image, 'uploads/demo/')) {
            $plot->image = DemoArt::save("plot-{$slug}", DemoArt::plotLayout($size, '30x50', 'Residential Plot', measurements: false));
        }

        $plot->save();

        $plot->seo()->updateOrCreate([], [
            'title' => "{$size} Plot in Model Town Khurrianwala — {$money($total)} on Installments",
            'description' => "{$size} residential plot: total {$money($total)}, booking {$money($booking)}, {$money($monthly)} monthly. 10% discount on cash payment.",
        ]);

        $plan = InstallmentPlan::withTrashed()->firstOrNew(['slug' => "{$slug}-residential-payment-plan"]);
        if ($plan->trashed()) {
            $plan->restore();
        }
        $plan->fill([
            'plot_id' => $plot->id,
            'name' => "{$size} Residential Payment Plan",
            'description' => '15% booking, 15% confirmation, '.self::MONTHS.' monthly installments, a payment every 6 months and 25% on possession.',
            'total_price' => $total,
            'full_payment_price' => $cash,
            'duration_months' => self::MONTHS,
            'terms' => implode(' ', self::TERMS),
            'is_default' => true,
            'is_featured' => true,
            'is_active' => true,
            'is_demo' => false,
            'sort_order' => 0,
        ])->save();

        $items = [
            [PaymentStageType::Booking, 'Booking', $booking, 1, 0, 'At the time of booking'],
            [PaymentStageType::Advance, 'Confirmation', $confirmation, 1, null, 'On confirmation'],
            [PaymentStageType::Monthly, 'Monthly Installments', $monthly, self::MONTHS, 1, self::MONTHS.' monthly installments'],
            [PaymentStageType::After6Months, 'After 6 Months', $sixMonthly, 1, 6, 'Month 6'],
            [PaymentStageType::After12Months, 'After 12 Months', $sixMonthly, 1, 12, 'Month 12'],
            [PaymentStageType::After18Months, 'After 18 Months', $sixMonthly, 1, 18, 'Month 18'],
            [PaymentStageType::After24Months, 'After 24 Months', $sixMonthly, 1, 24, 'Month 24'],
            [PaymentStageType::EndPayment, 'Possession', $possession, 1, null, 'At possession'],
        ];

        $plan->items()->delete();

        foreach ($items as $s => [$type, $title, $amount, $occurrences, $due, $label]) {
            $plan->items()->create([
                'type' => $type->value,
                'title' => $title,
                'amount' => $amount,
                'occurrences' => $occurrences,
                'percentage' => round($amount * $occurrences / $total * 100, 2),
                'due_after_months' => $due,
                'due_label' => $label,
                'sort_order' => $s,
            ]);
        }

        return [$plot, $plan];
    }

    protected function pages(): void
    {
        Page::query()->where('slug', 'plots')->update([
            'subheading' => 'Residential plots in 3 Marla, 5 Marla and 10 Marla — each with a clear installment plan.',
        ]);

        Page::query()->where('slug', 'installment-plans')->update([
            'subheading' => '15% booking, 15% confirmation, 24 monthly installments, a payment every 6 months and 25% on possession.',
        ]);
    }

    /**
     * Pricing answers that match the flyer; the separate development/possession
     * charges question is switched off because the flyer has no such charges.
     */
    protected function faqs(): void
    {
        $answers = [
            'What does an installment plan include?' => 'Every plan has a 15% booking payment, a 15% confirmation payment, 24 monthly installments, a payment after every 6 months and 25% on possession. For example, a 3 Marla plot is PKR 2,700,000 in total: PKR 405,000 booking, PKR 405,000 confirmation, PKR 28,125 a month, PKR 135,000 every 6 months and PKR 675,000 on possession.',
            'Can I pay the full amount at once?' => 'Yes. You get a 10% discount on cash payment.',
            'When will I get possession?' => 'Possession for construction is given on payment of 30% of the total price.',
        ];

        foreach ($answers as $question => $answer) {
            Faq::query()->where('question', $question)->update(['answer' => $answer]);
        }

        Faq::query()->where('question', 'Are development and possession charges included?')->update(['is_active' => false]);

        Faq::query()->updateOrCreate(['question' => 'Is there an extra charge for category plots?'], [
            'category' => 'Payments',
            'answer' => 'Yes. 10% extra is charged on category plots.',
            'sort_order' => (int) Faq::query()->where('question', 'Can I pay the full amount at once?')->value('sort_order'),
            'is_active' => true,
        ]);
    }
}
