<?php

namespace Tests\Feature;

use App\Enums\PlotStatus;
use App\Models\BookingRequest;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\Page;
use App\Models\Plot;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewSubmissionNotification;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function pages(): array
    {
        return collect([
            '/', '/about', '/plots', '/plots/5-marla', '/installment-plans', '/installment-plans?plot=10-marla', '/locations',
            '/locations/khurrianwala-main-site', '/development', '/gallery', '/testimonials', '/faq', '/contact',
            '/book-plot', '/book-plot?plot=5-marla&plan=5-marla-fast-track-plan', '/sitemap.xml', '/robots.txt',
            '/api/v1/plots', '/api/v1/plots/1-kanal', '/api/v1/installment-plans/3-marla-easy-installment-plan', '/api/v1/faqs',
        ])->mapWithKeys(fn ($url) => [$url => [$url]])->all();
    }

    #[DataProvider('pages')]
    public function test_public_pages_load(string $url): void
    {
        $this->get($url)->assertOk();
    }

    public function test_hidden_plot_and_disabled_page_return_404(): void
    {
        Plot::query()->where('slug', '3-marla')->update(['is_active' => false]);
        $this->get('/plots/3-marla')->assertNotFound();

        Page::query()->where('slug', 'gallery')->update(['is_active' => false]);
        $this->get('/gallery')->assertNotFound();
    }

    public function test_homepage_shows_admin_managed_prices_and_settings(): void
    {
        Setting::query()->where('key', 'phone')->update(['value' => '+92 41 7654321']);
        app(SettingsService::class)->flush();

        $this->get('/')->assertOk()->assertSee('+92 41 7654321')->assertSee('PKR 4,500,000');
    }

    public function test_inquiry_creates_lead_and_notifies_staff(): void
    {
        Notification::fake();
        $plot = Plot::query()->where('slug', '5-marla')->first();

        $this->postJson('/inquiry', ['name' => 'Test Buyer', 'phone' => '+92 300 1234567', 'plot_id' => $plot->id, 'source' => 'plot_page'])
            ->assertOk()->assertJsonStructure(['message']);

        $lead = Lead::query()->firstOrFail();
        $this->assertSame('Test Buyer', $lead->name);
        $this->assertSame('plot_page', $lead->source->value);
        $this->assertSame('new', $lead->status->value);
        $super = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();
        Notification::assertSentTo($super, NewSubmissionNotification::class);
    }

    public function test_inquiry_validation_and_honeypot(): void
    {
        $this->postJson('/inquiry', ['name' => '', 'phone' => 'abc'])->assertUnprocessable()->assertJsonValidationErrors(['name', 'phone']);
        $this->postJson('/inquiry', ['name' => 'Bot', 'phone' => '+92 300 1234567', 'website' => 'http://spam.test'])->assertUnprocessable();
        $this->assertSame(0, Lead::query()->count());
    }

    public function test_booking_request_creates_booking_and_lead_without_payment(): void
    {
        $plot = Plot::query()->where('slug', '5-marla')->with('activePlans')->first();

        $this->postJson('/book-plot', [
            'plot_id' => $plot->id, 'installment_plan_id' => $plot->activePlans->first()->id,
            'name' => 'Booking Buyer', 'phone' => '+92 300 7654321', 'cnic' => '35202-1234567-1', 'agree' => '1',
        ])->assertOk()->assertJsonStructure(['message', 'reference']);

        $booking = BookingRequest::query()->with('lead')->firstOrFail();
        $this->assertMatchesRegularExpression('/^BK-\d{4}-\d{4}$/', $booking->reference);
        $this->assertSame('pending', $booking->status->value);
        $this->assertSame('35202-1234567-1', $booking->cnic);
        $this->assertNotSame('35202-1234567-1', \DB::table('booking_requests')->value('cnic'), 'CNIC must be stored encrypted');
        $this->assertSame('booking', $booking->lead->source->value);
    }

    public function test_sold_out_plot_cannot_be_booked(): void
    {
        $plot = Plot::query()->where('slug', '1-kanal')->first();
        $plot->update(['status' => PlotStatus::SoldOut]);

        $this->postJson('/book-plot', ['plot_id' => $plot->id, 'name' => 'X', 'phone' => '+92 300 7654321', 'agree' => '1'])
            ->assertUnprocessable()->assertJsonValidationErrors('plot_id');
    }

    public function test_contact_message_and_newsletter(): void
    {
        $this->postJson('/contact', ['name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'Hello'])->assertOk();
        $this->postJson('/newsletter', ['email' => 'Reader@Example.com'])->assertOk();
        $this->postJson('/newsletter', ['email' => 'reader@example.com'])->assertOk();

        $this->assertSame(1, ContactMessage::query()->count());
        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_forms_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/callback', ['name' => 'Caller', 'phone' => '+92 300 1111111'])->assertOk();
        }

        $this->postJson('/callback', ['name' => 'Caller', 'phone' => '+92 300 1111111'])->assertStatus(429);
    }

    public function test_api_returns_schedule_totals(): void
    {
        $this->getJson('/api/v1/installment-plans/3-marla-easy-installment-plan')
            ->assertOk()
            ->assertJsonPath('data.summary.total_price', 2950000)
            ->assertJsonPath('data.summary.is_balanced', true)
            ->assertJsonPath('data.summary.installment_count', 36);
    }
}
