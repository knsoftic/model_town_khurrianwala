<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\InstallmentPlan;
use App\Models\Lead;
use App\Models\NavigationItem;
use App\Models\Plot;
use Database\Seeders\PaymentPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPlanSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
    }

    public function test_only_the_flyer_plots_and_plans_remain_and_every_plan_balances(): void
    {
        $lead = Lead::query()->create(['name' => 'Kanal buyer', 'phone' => '+92 300 1111111', 'plot_id' => Plot::query()->where('slug', '1-kanal')->value('id'), 'source' => 'inquiry', 'status' => 'new']);

        $this->seed(PaymentPlanSeeder::class);
        $this->seed(PaymentPlanSeeder::class);

        $this->assertSame(['3 Marla', '5 Marla', '10 Marla'], Plot::query()->ordered()->pluck('size_label')->all());
        $this->assertSame(3, InstallmentPlan::query()->count());
        $this->assertNotNull($lead->fresh(), 'Leads for removed sizes are kept');
        $this->assertFalse((bool) NavigationItem::query()->where('url', '/plots/1-kanal')->value('is_active'));

        foreach (PaymentPlanSeeder::PLANS as $size => [$total, $booking, $confirmation, $sixMonthly, $monthly, $possession]) {
            $plot = Plot::query()->where('size_label', $size)->with('activePlans.items')->firstOrFail();
            $plan = $plot->activePlans->sole();
            $summary = $plan->summary();

            $this->assertTrue($summary->isBalanced(), "{$size} schedule adds up to the total");
            $this->assertEquals($total, $summary->totalPrice);
            $this->assertSame(24, $summary->installmentCount);
            $this->assertEquals($monthly, $summary->installmentAmount);
            $this->assertEquals($sixMonthly * 4, $summary->amountFor('after_6_months', 'after_12_months', 'after_18_months', 'after_24_months'));
            $this->assertEquals($total * 0.9, (float) $plan->full_payment_price);
            $this->assertFalse($plot->is_demo_price);
            $this->assertEquals([$booking, $confirmation, $possession], [(float) $plot->booking_amount, (float) $plot->down_payment, (float) $plot->end_payment]);
        }

        $this->get('/plots/3-marla')->assertOk()
            ->assertSee('PKR 2,700,000')
            ->assertSee('Confirmation')
            ->assertSee('Every 6 Months')
            ->assertSee('PKR 135,000 × 4')
            ->assertSee('10% discount on cash payment')
            ->assertDontSee('Demo Price — for preview only');

        $this->get('/plots/1-kanal')->assertNotFound();
        $this->get('/installment-plans')->assertOk()->assertSee('PKR 46,875')->assertDontSee('Fast-Track');
        $this->get('/api/v1/plots')->assertOk()->assertJsonCount(3, 'data');

        $this->assertFalse((bool) Faq::query()->where('question', 'Are development and possession charges included?')->value('is_active'));
        $this->get('/faq')->assertOk()->assertSee('10% extra is charged on category plots.');
    }
}
