<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\InstallmentPlan;
use App\Models\Lead;
use App\Models\Plot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        $this->admin = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();
    }

    /**
     * @return array<int, string>
     */
    public static function adminPages(): array
    {
        return [
            '/admin', '/admin/plots', '/admin/plots/create', '/admin/plots/5-marla/edit', '/admin/plots/availability', '/admin/plots/featured',
            '/admin/installment-plans', '/admin/installment-plans/schedules', '/admin/installment-plans/create',
            '/admin/leads', '/admin/leads/create', '/admin/bookings', '/admin/messages', '/admin/subscribers',
            '/admin/sections', '/admin/sections/1/edit', '/admin/pages', '/admin/pages/about/edit', '/admin/menus',
            '/admin/features', '/admin/development', '/admin/statistics', '/admin/faqs', '/admin/locations', '/admin/testimonials',
            '/admin/gallery', '/admin/gallery/albums/1', '/admin/videos', '/admin/settings/general', '/admin/settings/appearance',
            '/admin/settings/notifications', '/admin/social-links', '/admin/users', '/admin/roles', '/admin/activity',
            '/admin/reports', '/admin/reports/monthly-inquiries', '/admin/notifications', '/admin/profile',
        ];
    }

    public function test_every_admin_page_loads_for_super_admin(): void
    {
        foreach (self::adminPages() as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_admin_can_create_a_plot_with_images(): void
    {
        $this->actingAs($this->admin)->post('/admin/plots', [
            'size_label' => '7 Marla', 'title' => '7 Marla Plot', 'category' => 'residential', 'payment_frequency' => 'monthly',
            'status' => 'available', 'starting_price' => 5000000, 'total_units' => 10, 'sold_units' => 2,
            'is_active' => '1', 'is_featured' => '0', 'is_demo_price' => '0',
            'image' => UploadedFile::fake()->image('main.jpg', 800, 600),
            'gallery' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
            'full_description' => '<p>Nice <script>alert(1)</script><strong>plot</strong></p>',
        ])->assertRedirect('/admin/plots/7-marla/edit');

        $plot = Plot::query()->where('slug', '7-marla')->with('images')->firstOrFail();
        Storage::disk('public')->assertExists($plot->image);
        $this->assertCount(2, $plot->images);
        $this->assertStringNotContainsString('<script', $plot->full_description);
        $this->assertStringContainsString('<strong>plot</strong>', $plot->full_description);
        $this->get('/plots/7-marla')->assertOk()->assertSee('7 Marla Plot');
    }

    public function test_sold_units_cannot_exceed_total(): void
    {
        $this->actingAs($this->admin)->post('/admin/plots', [
            'size_label' => '8 Marla', 'title' => '8 Marla', 'category' => 'residential', 'payment_frequency' => 'monthly',
            'status' => 'available', 'total_units' => 5, 'sold_units' => 9,
        ])->assertSessionHasErrors('sold_units');
    }

    public function test_installment_plan_schedule_is_saved_in_order_and_default_is_exclusive(): void
    {
        $plot = Plot::query()->where('slug', '5-marla')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/installment-plans', [
            'plot_id' => $plot->id, 'name' => 'Quick Plan', 'total_price' => 1000000, 'is_active' => '1', 'is_default' => '1',
            'items' => [
                ['type' => 'booking', 'title' => 'Booking', 'amount' => 100000, 'occurrences' => 1],
                ['type' => 'monthly', 'title' => 'Monthly', 'amount' => 50000, 'occurrences' => 12],
                ['type' => 'end_payment', 'title' => 'End', 'amount' => 300000, 'occurrences' => 1, 'due_after_months' => 12],
            ],
        ])->assertRedirect();

        $plan = InstallmentPlan::query()->where('name', 'Quick Plan')->with('items')->firstOrFail();
        $this->assertSame(['Booking', 'Monthly', 'End'], $plan->items->pluck('title')->all());
        $this->assertEquals(60.0, (float) $plan->items[1]->percentage);
        $this->assertTrue($plan->summary()->isBalanced());
        $this->assertSame(1, InstallmentPlan::query()->where('plot_id', $plot->id)->where('is_default', true)->count());

        $this->actingAs($this->admin)->post("/admin/installment-plans/{$plan->slug}/apply-to-plot")->assertRedirect();
        $this->assertEquals(50000, (float) $plot->fresh()->installment_amount);
        $this->assertSame(12, $plot->fresh()->installment_count);
    }

    public function test_duplicating_a_plot_copies_plans_and_starts_hidden(): void
    {
        $this->actingAs($this->admin)->post('/admin/plots/5-marla/duplicate')->assertRedirect('/admin/plots/5-marla-copy/edit');

        $copy = Plot::query()->where('slug', '5-marla-copy')->withCount('installmentPlans')->firstOrFail();
        $this->assertFalse($copy->is_active);
        $this->assertSame(2, $copy->installment_plans_count);
    }

    public function test_settings_and_homepage_sections_drive_the_website(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/appearance', ['color_primary' => '#123456'])->assertRedirect();
        $this->get('/')->assertSee('--brand-primary: #123456;', false);

        $this->actingAs($this->admin)->put('/admin/settings/appearance', ['color_primary' => 'red;}body{display:none'])->assertSessionHasErrors('color_primary');

        $this->actingAs($this->admin)->put('/admin/sections/1', [
            'name' => 'Hero', 'heading' => 'A Brand New Heading', 'is_active' => '1',
            'media' => ['background_image' => UploadedFile::fake()->image('hero.jpg', 1600, 900)],
        ])->assertRedirect();
        $this->get('/')->assertSee('A Brand New Heading');
    }

    public function test_google_map_embed_only_accepts_google_maps(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/contact', ['google_maps_embed' => '<iframe src="https://evil.test/x"></iframe>'])
            ->assertSessionHasErrors('google_maps_embed');

        $this->actingAs($this->admin)->put('/admin/settings/contact', [
            'google_maps_embed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18" width="600"></iframe>',
        ])->assertSessionHasNoErrors();
        $this->assertSame('https://www.google.com/maps/embed?pb=!1m18', setting('google_maps_embed'));
    }

    public function test_lead_quick_update_and_export_are_safe(): void
    {
        $lead = Lead::query()->create(['name' => '=HYPERLINK("http://x")', 'phone' => '+92 300 0000001', 'source' => 'inquiry', 'status' => 'new']);

        $this->actingAs($this->admin)->patchJson("/admin/leads/{$lead->id}/quick", ['status' => 'contacted'])->assertOk();
        $this->assertSame('contacted', $lead->fresh()->status->value);
        $this->assertNotNull($lead->fresh()->last_contacted_at);

        $csv = $this->actingAs($this->admin)->get('/admin/leads/export/csv')->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_reports_export_in_all_formats(): void
    {
        foreach (['csv' => 'text/csv', 'xlsx' => 'spreadsheetml', 'pdf' => 'application/pdf'] as $format => $type) {
            $response = $this->actingAs($this->admin)->get("/admin/reports/plot-sizes/export/{$format}");
            $response->assertOk();
            $this->assertStringContainsString($type, (string) $response->headers->get('Content-Type'));
        }
    }

    public function test_changes_are_written_to_the_activity_log(): void
    {
        $this->actingAs($this->admin)->put('/admin/faqs/1', ['question' => 'Updated question?', 'answer' => 'Yes.', 'is_active' => '1']);

        $this->assertDatabaseHas('activity_logs', ['action' => 'updated', 'subject_type' => Faq::class, 'subject_id' => 1, 'user_id' => $this->admin->id]);
    }
}
