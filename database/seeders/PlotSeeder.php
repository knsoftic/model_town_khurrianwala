<?php

namespace Database\Seeders;

use App\Enums\PaymentStageType;
use App\Enums\PlotStatus;
use App\Models\InstallmentPlan;
use App\Models\Location;
use App\Models\Plot;
use Database\Seeders\Support\DemoArt;
use Illuminate\Database\Seeder;

/**
 * Seeds the four default plot sizes with clearly-marked DEMO prices and schedules.
 * None of these figures are real company pricing.
 */
class PlotSeeder extends Seeder
{
    public function run(): void
    {
        $location = Location::query()->updateOrCreate(['slug' => 'khurrianwala-main-site'], [
            'name' => 'Khurrianwala — Main Project Site',
            'city' => 'Faisalabad',
            'area' => 'Khurrianwala',
            'address' => 'Project Site Office, Khurrianwala (demo address)',
            'short_description' => 'A well-connected site close to the main road, the city and daily essentials.',
            'description' => '<p>The project is located in Khurrianwala, Faisalabad, with quick access to the main road network and nearby schools, hospitals and markets.</p><p><em>Distances and landmarks below are demo values. Update them from Admin → Locations.</em></p>',
            'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Khurrianwala+Faisalabad',
            'map_embed_url' => 'https://www.google.com/maps?q=Khurrianwala,+Faisalabad&z=13&output=embed',
            'landmarks' => [
                ['name' => 'Main Road', 'distance' => '2 min (demo)'],
                ['name' => 'Khurrianwala City Centre', 'distance' => '5 min (demo)'],
                ['name' => 'Schools & Colleges', 'distance' => '5 min (demo)'],
                ['name' => 'Hospital', 'distance' => '10 min (demo)'],
                ['name' => 'Faisalabad City', 'distance' => '25 min (demo)'],
            ],
            'distance_airport' => '30 min (demo)',
            'distance_motorway' => '20 min (demo)',
            'distance_main_road' => '2 min (demo)',
            'image' => DemoArt::save('location-map', DemoArt::areaMap()),
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $location->seo()->updateOrCreate([], [
            'title' => 'Project Location — Khurrianwala, Faisalabad',
            'description' => 'See the project location, nearby landmarks and distances from the main road, motorway and airport.',
        ]);

        foreach ($this->plots() as $i => $data) {
            $schedules = $data['plans'];
            unset($data['plans']);

            $data['image'] = DemoArt::save('plot-'.$data['slug'], DemoArt::plotLayout($data['size_label'], $data['dimensions'], number_format($data['area_sqft']).' sq ft'));

            $plot = Plot::query()->updateOrCreate(['slug' => $data['slug']], $data + [
                'location_id' => $location->id,
                'category' => 'residential',
                'payment_frequency' => 'monthly',
                'is_active' => true,
                'is_demo_price' => true,
                'price_note' => 'Demo Price — for preview only',
                'sort_order' => $i,
            ]);

            $plot->seo()->updateOrCreate([], [
                'title' => $plot->size_label.' Plots on Installments',
                'description' => "Price, booking amount and complete installment plan for {$plot->size_label} plots.",
            ]);

            foreach ($schedules as $p => $plan) {
                $items = $plan['items'];
                unset($plan['items']);

                $model = InstallmentPlan::query()->updateOrCreate(['slug' => $plan['slug']], $plan + [
                    'plot_id' => $plot->id,
                    'is_default' => $p === 0,
                    'is_active' => true,
                    'is_demo' => true,
                    'sort_order' => $p,
                    'terms' => 'Demo plan for preview only. Development and possession charges are payable separately as per company policy.',
                ]);

                $model->items()->delete();
                foreach ($items as $s => [$type, $title, $amount, $occurrences, $due, $label]) {
                    $model->items()->create([
                        'type' => $type->value,
                        'title' => $title,
                        'amount' => $amount,
                        'occurrences' => $occurrences,
                        'percentage' => round($amount * $occurrences / $plan['total_price'] * 100, 2),
                        'due_after_months' => $due,
                        'due_label' => $label,
                        'sort_order' => $s,
                    ]);
                }
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function plots(): array
    {
        return [
            $this->plot('3 Marla', '3-marla', '25x27', 675, 2750000, 2950000, 150000, 450000, 45000, 200000, 200000, 330000, 150000, 25000, 320, 214, PlotStatus::Available, false,
                'Compact and affordable — ideal for a first home.'),
            $this->plot('5 Marla', '5-marla', '25x45', 1125, 4500000, 4850000, 250000, 700000, 75000, 300000, 300000, 600000, 250000, 35000, 450, 301, PlotStatus::Available, true,
                'Our most popular size for growing families.', [
                    [
                        'name' => '5 Marla Fast-Track 24-Month Plan',
                        'slug' => '5-marla-fast-track-plan',
                        'description' => 'A shorter plan with a higher monthly installment and fewer milestone payments.',
                        'total_price' => 4700000,
                        'full_payment_price' => 4500000,
                        'duration_months' => 24,
                        'items' => [
                            [PaymentStageType::Booking, 'Booking', 250000, 1, 0, 'At the time of booking'],
                            [PaymentStageType::Advance, 'Advance Payment', 950000, 1, 1, 'Within 30 days of booking'],
                            [PaymentStageType::Monthly, 'Monthly Installments', 110000, 24, 1, '24 monthly installments'],
                            [PaymentStageType::After12Months, 'After 12 Months', 400000, 1, 12, 'Due in month 12'],
                            [PaymentStageType::EndPayment, 'End / Possession Payment', 460000, 1, 24, 'At possession'],
                        ],
                    ],
                ]),
            $this->plot('10 Marla', '10-marla', '35x65', 2275, 8300000, 8900000, 450000, 1300000, 135000, 550000, 550000, 1190000, 450000, 50000, 180, 140, PlotStatus::Limited, true,
                'Spacious plots on wide roads, close to parks.', [
                    [
                        'name' => '10 Marla Quarterly Plan',
                        'slug' => '10-marla-quarterly-plan',
                        'description' => 'Pay every three months instead of monthly.',
                        'total_price' => 8900000,
                        'full_payment_price' => 8300000,
                        'duration_months' => 36,
                        'items' => [
                            [PaymentStageType::Booking, 'Booking', 450000, 1, 0, 'At the time of booking'],
                            [PaymentStageType::Advance, 'Advance Payment', 1300000, 1, 1, 'Within 30 days of booking'],
                            [PaymentStageType::Quarterly, 'Quarterly Installments', 480000, 12, 3, '12 quarterly installments'],
                            [PaymentStageType::EndPayment, 'End / Possession Payment', 1390000, 1, 36, 'At possession'],
                        ],
                    ],
                ]),
            $this->plot('1 Kanal', '1-kanal', '50x90', 4500, 15400000, 16500000, 800000, 2500000, 250000, 1000000, 1000000, 2200000, 800000, 75000, 60, 52, PlotStatus::Limited, false,
                'Premium large plots in a prime block.'),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $extraPlans
     * @return array<string, mixed>
     */
    protected function plot(
        string $size, string $slug, string $dimensions, int $sqft, int $lumpSum, int $total, int $booking, int $advance,
        int $monthly, int $after6, int $after12, int $end, int $development, int $possession, int $units, int $sold,
        PlotStatus $status, bool $featured, string $tagline, array $extraPlans = [],
    ): array {
        return [
            'size_label' => $size,
            'slug' => $slug,
            'title' => "{$size} Residential Plot",
            'short_description' => $tagline.' Booking from '.number_format($booking).' with easy monthly installments.',
            'full_description' => "<p>{$tagline} The {$size} plot ({$dimensions} ft, approx. ".number_format($sqft).' sq ft) comes with a clear 36-month installment plan, so you know every payment from booking to possession.</p>'
                .'<ul><li>Located on planned streets with access to parks and the commercial area</li><li>Transparent schedule — booking, advance, monthly installments, milestone payments and possession</li><li>Lump-sum payment option available</li></ul>'
                .'<p><em>All prices shown are demo values for preview only and are not real company pricing.</em></p>',
            'dimensions' => $dimensions,
            'area_sqft' => $sqft,
            'starting_price' => $lumpSum,
            'total_price' => $total,
            'booking_amount' => $booking,
            'down_payment' => $advance,
            'installment_count' => 36,
            'installment_amount' => $monthly,
            'after_6_months_payment' => $after6,
            'after_12_months_payment' => $after12,
            'end_payment' => $end,
            'development_charges' => $development,
            'possession_charges' => $possession,
            'total_units' => $units,
            'sold_units' => $sold,
            'status' => $status->value,
            'is_featured' => $featured,
            'plans' => array_merge([[
                'name' => "{$size} Easy Installment Plan",
                'slug' => "{$slug}-easy-installment-plan",
                'description' => "Our standard 36-month plan for {$size} plots.",
                'total_price' => $total,
                'full_payment_price' => $lumpSum,
                'duration_months' => 36,
                'is_featured' => true,
                'items' => [
                    [PaymentStageType::Booking, 'Booking', $booking, 1, 0, 'At the time of booking'],
                    [PaymentStageType::Advance, 'Advance Payment', $advance, 1, 1, 'Within 30 days of booking'],
                    [PaymentStageType::Monthly, 'Monthly Installments', $monthly, 36, 1, '36 monthly installments'],
                    [PaymentStageType::After6Months, 'After 6 Months', $after6, 1, 6, 'Due in month 6'],
                    [PaymentStageType::After12Months, 'After 12 Months', $after12, 1, 12, 'Due in month 12'],
                    [PaymentStageType::EndPayment, 'End / Possession Payment', $end, 1, 36, 'At possession'],
                ],
            ]], $extraPlans),
        ];
    }
}
