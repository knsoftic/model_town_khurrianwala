<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Location;
use App\Models\ProjectFeature;
use App\Models\Statistic;
use App\Models\User;
use Database\Seeders\ProjectDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDataSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
    }

    public function test_real_project_details_replace_the_demo_content(): void
    {
        $users = User::query()->count();
        $faqs = Faq::query()->count();

        $this->seed(ProjectDataSeeder::class);
        $this->seed(ProjectDataSeeder::class);

        $this->assertSame($users, User::query()->count(), 'Users are never touched');
        $this->assertSame($faqs + 3, Faq::query()->count(), 'Running it twice adds the new questions once');

        $this->assertSame('+92 300 8653838', setting('phone'));
        $this->assertSame('+92 308 8653838', setting('phone_secondary'));
        $this->assertSame('https://wa.me/923008653838', strtok((string) whatsapp_url(), '?'));
        $this->assertSame('', (string) setting('email'));

        $location = Location::query()->where('slug', 'khurrianwala-main-site')->firstOrFail();
        $this->assertSame([ProjectDataSeeder::LATITUDE, ProjectDataSeeder::LONGITUDE], [$location->latitude, $location->longitude]);

        $this->assertSame(count(ProjectDataSeeder::FEATURES), ProjectFeature::query()->where('is_active', true)->count());
        $this->assertFalse(ProjectFeature::query()->where('title', 'Commercial Area')->value('is_active'));
        $this->assertSame(['Wide Main Roads', 'Wide Streets', 'Security', 'Community Amenities'],
            Statistic::query()->where('group', 'intro')->where('is_active', true)->orderBy('sort_order')->pluck('label')->all());

        $this->get('/')->assertOk()
            ->assertSee('Own Your Plot in Model Town Khurrianwala')
            ->assertSee('Main Bazar Khurrianwala, Jhumra Road')
            ->assertSee('0300-8653838')
            ->assertSee('Mineral Water Supply')
            ->assertSee('maps?q=31.510134,73.257767', false)
            ->assertSee('"geo":{"@type":"GeoCoordinates","latitude":31.510134,"longitude":73.257767}', false)
            ->assertDontSee('info@example.com')
            ->assertDontSee('+92 300 0000000')
            ->assertDontSee('Happy Clients</p>', false);

        $this->get('/about')->assertOk()->assertSee('Solid waste management')->assertDontSee('This is demo content');
        $this->get('/faq')->assertOk()->assertSee('Where is Model Town Khurrianwala located?');
        $this->get('/llms.txt')->assertOk()->assertSee('Phone: +92 300 8653838');
    }
}
