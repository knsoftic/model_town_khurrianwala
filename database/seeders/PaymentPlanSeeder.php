<?php

namespace Database\Seeders;

use App\Enums\PaymentStageType;
use App\Enums\PlotStatus;
use App\Models\Faq;
use App\Models\HomepageSection;
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
 * Residential payment plans for 3, 5 and 10 Marla, in four steps and with no
 * booking fee:
 *
 *   1. Down payment (30%)
 *   2. Monthly installments (24)
 *   3. 6-month installments (4 — months 6, 12, 18 and 24)
 *   4. Final payment on registry (25%) — the payment is then complete
 *
 * The amounts are the official flyer's, with its booking and confirmation merged into
 * the down payment and its possession payment taken on registry. Every other plot
 * size and plan is moved to the trash (leads and bookings that mention them are
 * kept), so the website shows only these; more can still be added in the admin.
 *
 *   php artisan db:seed --class=PaymentPlanSeeder --force
 */
class PaymentPlanSeeder extends Seeder
{
    public const MONTHS = 24;

    public const SIX_MONTHLY = 4;

    /**
     * size => [total, down payment, monthly, every 6 months, on registry]
     *
     * @var array<string, array{0: int, 1: int, 2: int, 3: int, 4: int}>
     */
    public const PLANS = [
        '3 Marla' => [2700000, 810000, 28125, 135000, 675000],
        '5 Marla' => [4500000, 1350000, 46875, 225000, 1125000],
        '10 Marla' => [9000000, 2700000, 93750, 450000, 2250000],
    ];

    public const CASH_DISCOUNT = 0.10;

    public const TERMS = [
        'No booking fee.',
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

            // Only these sizes and plans remain on the website.
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
        [$total, $down, $monthly, $sixMonthly, $registry] = self::PLANS[$size];
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
            'short_description' => "Total price {$money($total)} — down payment {$money($down)}, then {$money($monthly)} a month. No booking fee.",
            'full_description' => "<p>{$size} residential plot in Model Town Khurrianwala, Faisalabad, on an easy installment plan with no booking fee. Total price {$money($total)}.</p>"
                .'<ol>'
                ."<li>Down payment: {$money($down)}</li>"
                ."<li>Monthly installments: {$money($monthly)} × ".self::MONTHS.'</li>'
                ."<li>6-month installments: {$money($sixMonthly)} × ".self::SIX_MONTHLY.' (every 6 months)</li>'
                ."<li>On registry: {$money($registry)} — your payment is then complete</li>"
                .'</ol>'
                .'<h3>Terms and conditions</h3><ul>'.collect(self::TERMS)->map(fn (string $term) => '<li>'.rtrim($term, '.').'</li>')->implode('').'</ul>',
            'dimensions' => null,
            'area_sqft' => null,
            'starting_price' => null,
            'total_price' => $total,
            'booking_amount' => null,
            'down_payment' => $down,
            'installment_count' => self::MONTHS,
            'installment_amount' => $monthly,
            'payment_frequency' => 'monthly',
            'after_6_months_payment' => $sixMonthly,
            'after_12_months_payment' => null,
            'end_payment' => $registry,
            'development_charges' => null,
            'possession_charges' => null,
            'total_units' => 0,
            'sold_units' => 0,
            'status' => PlotStatus::Available,
            'is_featured' => true,
            'is_active' => true,
            'is_demo_price' => false,
            'price_note' => 'No booking fee · 10% discount on cash payment · 10% extra on category plots',
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
            'title' => "{$size} Plot for Sale in Faisalabad",
            'description' => "{$size} residential plot: total {$money($total)}, down payment {$money($down)}, {$money($monthly)} monthly. No booking fee, 10% off on cash payment.",
        ]);

        $plan = InstallmentPlan::withTrashed()->firstOrNew(['slug' => "{$slug}-residential-payment-plan"]);
        if ($plan->trashed()) {
            $plan->restore();
        }
        $plan->fill([
            'plot_id' => $plot->id,
            'name' => "{$size} Residential Payment Plan",
            'description' => 'Down payment, '.self::MONTHS.' monthly installments, a 6-month installment every 6 months and the final payment on registry. No booking fee.',
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
            [PaymentStageType::Advance, 'Down Payment', $down, 1, 0, 'Paid upfront — no booking fee'],
            [PaymentStageType::Monthly, 'Monthly Installments', $monthly, self::MONTHS, 1, self::MONTHS.' monthly installments'],
            [PaymentStageType::HalfYearly, '6-Month Installments', $sixMonthly, self::SIX_MONTHLY, 6, 'Every 6 months (months 6, 12, 18 and 24)'],
            [PaymentStageType::Registry, 'On Registry', $registry, 1, null, 'Final payment on registry — payment complete'],
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
            'subheading' => 'Residential plots in 3 Marla, 5 Marla and 10 Marla — each with a clear installment plan and no booking fee.',
        ]);

        Page::query()->where('slug', 'installment-plans')->update([
            'subheading' => 'Down payment, monthly installments, 6-month installments and the final payment on registry. No booking fee.',
        ]);

        HomepageSection::query()->where('key', 'calculator')->update([
            'subheading' => 'Select a plot size to see the total price, down payment, monthly and 6-month installments and the final payment on registry.',
        ]);

        HomepageSection::query()->where('key', 'installment_plans')->update([
            'subheading' => 'Four simple steps: down payment, monthly installments, 6-month installments and the final payment on registry.',
        ]);
    }

    /**
     * Pricing answers for the four-step plan; the separate development/possession
     * charges question is switched off because there are no such charges.
     */
    protected function faqs(): void
    {
        $answers = [
            'What does an installment plan include?' => 'Every plan has four steps and no booking fee: a 30% down payment, 24 monthly installments, a 6-month installment every 6 months (4 in total) and the final payment on registry. For example, a 3 Marla plot is PKR 2,700,000: PKR 810,000 down payment, PKR 28,125 a month, PKR 135,000 every 6 months and PKR 675,000 on registry.',
            'Can I pay the full amount at once?' => 'Yes. You get a 10% discount on cash payment.',
            'When will I get possession?' => 'Possession for construction is given on payment of 30% of the total price — that is, once the down payment is made. The final payment is made on registry.',
        ];

        foreach ($answers as $question => $answer) {
            Faq::query()->where('question', $question)->update(['answer' => $answer]);
        }

        Faq::query()->where('question', 'Which documents are required for booking?')->get()
            ->each(fn (Faq $faq) => $faq->update(['answer' => str_replace('and the booking amount', 'and the down payment', $faq->answer)]));

        Faq::query()->where('question', 'Are development and possession charges included?')->update(['is_active' => false]);

        $order = (int) Faq::query()->where('question', 'Can I pay the full amount at once?')->value('sort_order');

        foreach ([
            ['Is there a booking fee?', 'No. There is no booking fee — you start directly with the down payment.'],
            ['When is the last payment made?', 'The last payment is made on registry. Once it is paid, your payment is complete.'],
            ['Is there an extra charge for category plots?', 'Yes. 10% extra is charged on category plots.'],
        ] as [$question, $answer]) {
            Faq::query()->updateOrCreate(['question' => $question], ['category' => 'Payments', 'answer' => $answer, 'sort_order' => $order, 'is_active' => true]);
        }
    }
}
