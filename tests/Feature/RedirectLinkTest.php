<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        $this->admin = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();
    }

    public function test_short_link_sends_visitors_to_the_saved_destination(): void
    {
        $this->get('/leads')->assertNotFound();

        $this->actingAs($this->admin)->get('/admin/settings/redirect')->assertOk()->assertSee('Your short link')->assertSee('Not active yet');

        $this->actingAs($this->admin)->put('/admin/settings/redirect', [
            'redirect_enabled' => '1',
            'redirect_path' => 'leads',
            'redirect_url' => 'https://whatsapp.com/channel/0029VaTest',
        ])->assertSessionHasNoErrors();

        $this->get('/leads')->assertRedirect('https://whatsapp.com/channel/0029VaTest')
            ->assertStatus(302)
            ->assertHeader('X-Robots-Tag', 'noindex');
        $this->assertStringContainsString('no-store', (string) $this->get('/LEADS/')->headers->get('Cache-Control'));

        // A new destination works straight away for the same short link.
        $this->actingAs($this->admin)->put('/admin/settings/redirect', [
            'redirect_enabled' => '1', 'redirect_path' => 'leads', 'redirect_url' => 'https://chat.whatsapp.com/Community123',
        ]);
        $this->get('/leads')->assertRedirect('https://chat.whatsapp.com/Community123');
    }

    public function test_the_short_link_name_can_be_changed_and_switched_off(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/redirect', [
            'redirect_enabled' => '1', 'redirect_path' => ' https://mtkfsd.com/Join-Us/ ', 'redirect_url' => 'https://youtube.com/@channel',
        ])->assertSessionHasNoErrors();

        $this->assertSame('join-us', setting('redirect_path'));
        $this->get('/join-us')->assertRedirect('https://youtube.com/@channel');
        $this->get('/leads')->assertNotFound();

        $this->actingAs($this->admin)->put('/admin/settings/redirect', [
            'redirect_enabled' => '0', 'redirect_path' => 'join-us', 'redirect_url' => 'https://youtube.com/@channel',
        ]);
        $this->get('/join-us')->assertNotFound();
    }

    public function test_existing_pages_and_unsafe_links_are_rejected(): void
    {
        foreach (['plots', 'admin', 'about', 'storage', 'api', 'sitemap.xml', 'bad name'] as $path) {
            $this->actingAs($this->admin)->put('/admin/settings/redirect', [
                'redirect_enabled' => '1', 'redirect_path' => $path, 'redirect_url' => 'https://example.org',
            ])->assertSessionHasErrors('redirect_path');
        }

        $this->actingAs($this->admin)->put('/admin/settings/redirect', [
            'redirect_enabled' => '1', 'redirect_path' => 'leads', 'redirect_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('redirect_url');

        $this->get('/plots')->assertOk();
        $this->get('/some-unknown-page')->assertNotFound()->assertSee('This page could not be found');
    }
}
