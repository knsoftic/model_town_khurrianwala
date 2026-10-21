<?php

namespace Tests\Feature;

use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\User;
use App\Services\SeoGuide;
use App\Services\SeoService;
use App\Services\SettingsService;
use Database\Seeders\SeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        $this->admin = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();
    }

    public function test_public_pages_print_robots_and_publisher_tags_and_header(): void
    {
        $this->get('/plots')->assertOk()
            ->assertHeader('X-Robots-Tag', SeoService::INDEX)
            ->assertSee('<meta name="robots" content="'.SeoService::INDEX.'">', false)
            ->assertSee('<meta name="publisher" content="'.setting('site_name').'">', false)
            ->assertSee('<meta name="author" content="'.setting('site_name').'">', false)
            ->assertSee('<meta property="og:locale" content="en_PK">', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        $this->get('/admin/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_a_page_marked_noindex_says_so(): void
    {
        Page::query()->where('slug', 'gallery')->firstOrFail()->seo()->updateOrCreate([], ['robots' => 'noindex, follow']);

        $this->get('/gallery')->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_switching_indexing_off_hides_the_whole_website(): void
    {
        app(SettingsService::class)->update('seo', ['seo_indexing' => '0']);

        $this->get('/')->assertOk()
            ->assertHeader('X-Robots-Tag', SeoService::NOINDEX)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $robots = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString("Disallow: /\n", $robots);
        $this->assertStringNotContainsString('Sitemap:', $robots);
    }

    public function test_robots_txt_adds_extra_rules_before_the_sitemap(): void
    {
        app(SettingsService::class)->update('seo', ['robots_txt_extra' => "Disallow: /private\r\nDisallow: /drafts"]);

        $this->assertSame(
            "User-agent: *\nDisallow:\nDisallow: /private\nDisallow: /drafts\n\nSitemap: ".route('sitemap')."\n",
            $this->get('/robots.txt')->assertOk()->getContent(),
        );
    }

    public function test_a_title_that_names_the_website_is_not_given_the_name_twice(): void
    {
        $name = setting('site_name');
        Page::query()->where('slug', 'plots')->firstOrFail()->seo()->updateOrCreate([], ['title' => "Plots for Sale | {$name}"]);
        Page::query()->where('slug', 'faq')->firstOrFail()->seo()->updateOrCreate([], ['title' => 'Questions']);

        $this->get('/plots')->assertSee("<title>Plots for Sale | {$name}</title>", false);
        $this->get('/faq')->assertSee("<title>Questions | {$name}</title>", false);
    }

    public function test_keywords_added_in_settings_are_cleaned_and_printed(): void
    {
        $this->actingAs($this->admin)->get('/admin/settings/seo')->assertOk()
            ->assertSee('keywordInput', false)
            ->assertSee(route('admin.seo-guide'), false);

        $many = collect(range(1, 30))->map(fn ($i) => "plot keyword number {$i}")->implode(', ');

        $this->actingAs($this->admin)->put('/admin/settings/seo', [
            'seo_title' => 'Plots in Faisalabad',
            'seo_indexing' => '1',
            'seo_keywords' => " plots in Faisalabad ,5 Marla  plot\n5 marla PLOT,, {$many}",
        ])->assertSessionHasNoErrors()->assertRedirect();

        $expected = "plots in Faisalabad, 5 Marla plot, {$many}";
        $this->assertSame($expected, setting('seo_keywords'));
        $this->get('/plots')->assertSee('<meta name="keywords" content="'.$expected.'">', false);
    }

    public function test_seo_guide_is_for_staff_with_the_settings_permission(): void
    {
        $this->get('/admin/seo-guide')->assertRedirect(route('admin.login'));
        $this->actingAs($this->staff('sales-manager'))->get('/admin/seo-guide')->assertForbidden();

        $this->actingAs($this->admin)->get('/admin/seo-guide')->assertOk()
            ->assertSee('Is guide ka private link')
            ->assertSee(app(SeoGuide::class)->shareUrl(), false)
            ->assertSee('Technical SEO — aap ka flow')
            ->assertSee('Page-by-page check');
    }

    public function test_private_link_opens_the_guide_alone_without_a_login(): void
    {
        $url = app(SeoGuide::class)->shareUrl();

        $this->get($url)->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('Off-page SEO')
            ->assertSee(route('admin.settings.edit', 'seo'), false)
            ->assertDontSee('aria-label="Admin"', false)
            ->assertDontSee('Is guide ka private link');

        $this->get('/seo-guide/not-the-token')->assertNotFound();
    }

    public function test_signed_in_staff_are_taken_from_the_private_link_to_the_admin_guide(): void
    {
        $this->actingAs($this->admin)->get(app(SeoGuide::class)->shareUrl())->assertRedirect(route('admin.seo-guide'));
    }

    public function test_a_new_link_stops_the_old_one(): void
    {
        $old = app(SeoGuide::class)->shareUrl();

        $this->actingAs($this->admin)->post('/admin/seo-guide/link')->assertRedirect(route('admin.seo-guide'));
        auth()->logout();

        $this->get($old)->assertNotFound();
        $this->get(app(SeoGuide::class)->shareUrl())->assertOk();
    }

    public function test_seo_seeder_fills_defaults_and_keeps_edits_made_in_the_admin(): void
    {
        $settings = app(SettingsService::class);
        $settings->update('seo', ['seo_keywords' => 'my own keyword, plots for sale in Faisalabad']);
        Page::query()->where('slug', 'faq')->firstOrFail()->seo()->updateOrCreate([], ['title' => 'My own FAQ title', 'keywords' => 'faq keyword']);

        $this->seed(SeoSeeder::class);
        $this->seed(SeoSeeder::class);

        $plots = Page::query()->where('slug', 'plots')->firstOrFail()->seo;
        $this->assertSame(SeoSeeder::PAGES['plots'][0], $plots->title);
        $this->assertSame(SeoSeeder::PAGES['plots'][1], $plots->description);
        $this->assertSame(implode(', ', SeoSeeder::PAGES['plots'][2]), $plots->keywords);

        $faq = Page::query()->where('slug', 'faq')->firstOrFail()->seo;
        $this->assertSame('My own FAQ title', $faq->title);
        $this->assertSame('faq keyword', $faq->keywords);
        $this->assertSame(SeoSeeder::PAGES['faq'][1], $faq->description);

        $keywords = $settings->get('seo_keywords');
        $this->assertStringStartsWith('my own keyword, plots for sale in Faisalabad, Model Town Khurrianwala', $keywords);
        $this->assertSame(1, substr_count(strtolower($keywords), 'plots for sale in faisalabad,'));
        $this->assertSame(SeoSeeder::TITLE, $settings->get('seo_title'));

        $websitePages = array_slice(app(SeoGuide::class)->pages(), 0, count(NavigationItem::ROUTES));
        $this->assertSame([], collect($websitePages)->where('ok', false)->pluck('name')->all());
    }
}
