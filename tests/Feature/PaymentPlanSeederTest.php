<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\InstallmentPlan;
use App\Models\Lead;
use App\Models\NavigationItem;
use App\Models\Plot;
use App\Models\User;
use App\Services\InstallmentPlanService;
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

    public function test_plans_follow_the_four_step_flow_and_every_plan_balances(): void
    {
        $lead = Lead::query()->create(['name' => 'Kanal buyer', 'phone' => '+92 300 1111111', 'plot_id' => Plot::query()->where('slug', '1-kanal')->value('id'), 'source' => 'inquiry', 'status' => 'new']);

        $this->seed(PaymentPlanSeeder::class);
        $this->seed(PaymentPlanSeeder::class);

        $this->assertSame(['3 Marla', '5 Marla', '10 Marla'], Plot::query()->ordered()->pluck('size_label')->all());
        $this->assertSame(3, InstallmentPlan::query()->count());
        $this->assertNotNull($lead->fresh(), 'Leads for removed sizes are kept');
        $this->assertFalse((bool) NavigationItem::query()->where('url', '/plots/1-kanal')->value('is_active'));

        foreach (PaymentPlanSeeder::PLANS as $size => [$total, $down, $monthly, $sixMonthly, $registry]) {
            $plot = Plot::query()->where('size_label', $size)->with('activePlans.items')->firstOrFail();
            $plan = $plot->activePlans->sole();
            $summary = $plan->summary();

            $this->assertSame(['advance', 'monthly', 'half_yearly', 'registry'], $plan->items->map(fn ($item) => $item->type->value)->all(), 'No booking step');
            $this->assertTrue($summary->isBalanced(), "{$size} schedule adds up to the total");
            $this->assertEquals($total, $summary->totalPrice);
            $this->assertSame(24, $summary->installmentCount, '6-month installments are not counted as monthly ones');
            $this->assertEquals($monthly, $summary->installmentAmount);
            $this->assertEquals($sixMonthly * 4, $summary->amountFor('half_yearly'));
            $this->assertEquals($registry, $summary->amountFor('registry'));
            $this->assertSame(24, $summary->durationMonths);
            $this->assertEquals($total * 0.9, (float) $plan->full_payment_price);
            $this->assertNull($plot->booking_amount);
            $this->assertEquals([$down, $sixMonthly, $registry], [(float) $plot->down_payment, (float) $plot->after_6_months_payment, (float) $plot->end_payment]);
        }

        $this->get('/plots/3-marla')->assertOk()
            ->assertSee('PKR 2,700,000')
            ->assertSee('Down Payment')
            ->assertSee('PKR 810,000')
            ->assertSee('6-Month Installments')
            ->assertSee('PKR 135,000 × 4')
            ->assertSee('On Registry')
            ->assertSee('No booking fee')
            ->assertDontSee('Booking Amount');

        $this->get('/')->assertOk()->assertSee('Down Payment PKR 810,000');
        $this->get('/plots/1-kanal')->assertNotFound();
        $this->get('/installment-plans')->assertOk()->assertSee('PKR 46,875')->assertSee('On Registry')->assertDontSee('Fast-Track');
        $this->get('/api/v1/plots')->assertOk()->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.plans.0.items.2.repeat_text', '× 4 (every 6 months)');

        $this->assertFalse((bool) Faq::query()->where('question', 'Are development and possession charges included?')->value('is_active'));
        $this->get('/faq')->assertOk()->assertSee('Is there a booking fee?')->assertSee('The last payment is made on registry.');
    }

    public function test_admin_can_save_a_plan_with_6_month_installments_and_registry_payment(): void
    {
        $admin = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();
        $plot = Plot::query()->where('slug', '3-marla')->firstOrFail();

        $this->actingAs($admin)->post('/admin/installment-plans', [
            'plot_id' => $plot->id, 'name' => 'Custom 4-step', 'total_price' => 1000000, 'is_active' => '1',
            'items' => [
                ['type' => 'advance', 'title' => 'Down Payment', 'amount' => 300000, 'occurrences' => 1, 'due_after_months' => 0],
                ['type' => 'monthly', 'title' => 'Monthly', 'amount' => 10000, 'occurrences' => 24, 'due_after_months' => 1],
                ['type' => 'half_yearly', 'title' => '6-Month', 'amount' => 50000, 'occurrences' => 4, 'due_after_months' => 6],
                ['type' => 'registry', 'title' => 'On Registry', 'amount' => 260000, 'occurrences' => 1],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $plan = InstallmentPlan::query()->where('name', 'Custom 4-step')->with('items')->firstOrFail();
        $this->assertSame(4, $plan->items[2]->occurrences, '6-month installments keep their count');
        $this->assertTrue($plan->summary()->isBalanced());
        $this->assertSame(24, $plan->summary()->installmentCount);
    }

    public function test_applying_a_four_step_plan_fills_the_plot_card_fields(): void
    {
        $this->seed(PaymentPlanSeeder::class);
        $plot = Plot::query()->where('slug', '5-marla')->firstOrFail();
        $plot->update(['down_payment' => null, 'after_6_months_payment' => null, 'end_payment' => null, 'installment_count' => null]);

        app(InstallmentPlanService::class)->applyToPlot($plot->installmentPlans()->firstOrFail());

        $plot->refresh();
        $this->assertEquals([1350000, 46875, 24, 225000, 1125000], [
            (float) $plot->down_payment, (float) $plot->installment_amount, $plot->installment_count, (float) $plot->after_6_months_payment, (float) $plot->end_payment,
        ]);
        $this->assertSame('monthly', $plot->payment_frequency);
    }
}
