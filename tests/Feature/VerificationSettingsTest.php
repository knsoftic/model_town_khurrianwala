<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\VerificationTags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
        $this->admin = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();
    }

    public function test_pasted_tags_and_codes_are_saved_clean_and_printed_in_head(): void
    {
        $this->actingAs($this->admin)->get('/admin/settings/verification')->assertOk()->assertSee('Google Search Console');

        $this->actingAs($this->admin)->put('/admin/settings/verification', [
            'google_site_verification' => '<meta name="google-site-verification" content="AbC_123-xyz" />',
            'bing_site_verification' => '0123456789ABCDEF0123456789ABCDEF',
            'facebook_domain_verification' => "<meta name='facebook-domain-verification' content='fbcode123' />",
            'custom_meta_tags' => implode("\n", [
                '<meta name="ahrefs-site-verification" content="ahrefs999">',
                '<meta content="baidu-code" name="baidu-site-verification" />',
                '<meta http-equiv="refresh" content="0;url=https://evil.test">',
                '<meta name="robots" content="noindex">',
                '<script>alert(1)</script>',
            ]),
            'google_analytics_id' => "<script async src=\"https://www.googletagmanager.com/gtag/js?id=G-TEST1234\"></script>\n<script>gtag('config', 'G-TEST1234');</script>",
            'google_tag_manager_id' => 'gtm-abc123',
            'analytics_delay' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('AbC_123-xyz', setting('google_site_verification'));
        $this->assertSame('fbcode123', setting('facebook_domain_verification'));
        $this->assertSame('G-TEST1234', setting('google_analytics_id'));
        $this->assertSame('GTM-ABC123', setting('google_tag_manager_id'));
        $this->assertSame(
            "<meta name=\"ahrefs-site-verification\" content=\"ahrefs999\">\n<meta name=\"baidu-site-verification\" content=\"baidu-code\">",
            setting('custom_meta_tags'),
        );

        $this->get('/')->assertOk()
            ->assertSee('<meta name="google-site-verification" content="AbC_123-xyz">', false)
            ->assertSee('<meta name="msvalidate.01" content="0123456789ABCDEF0123456789ABCDEF">', false)
            ->assertSee('<meta name="facebook-domain-verification" content="fbcode123">', false)
            ->assertSee('<meta name="ahrefs-site-verification" content="ahrefs999">', false)
            ->assertSee('<meta name="baidu-site-verification" content="baidu-code">', false)
            ->assertSee('js?id=G-TEST1234', false)
            ->assertSee('gtm.js?id=GTM-ABC123', false)
            ->assertSee('googletagmanager.com/ns.html?id=GTM-ABC123', false)
            ->assertSee('setTimeout(load, 6000)', false)
            ->assertDontSee('evil.test', false)
            ->assertDontSee('content="noindex"', false)
            ->assertDontSee('alert(1)', false);
    }

    public function test_analytics_can_load_straight_away(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/verification', ['google_analytics_id' => 'G-NOW1234', 'analytics_delay' => '0'])
            ->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertSee('js?id=G-NOW1234', false)->assertDontSee('setTimeout(load, 6000)', false);
    }

    public function test_unusable_input_is_rejected(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings/verification', [
            'google_site_verification' => '<script>alert(1)</script>',
            'custom_meta_tags' => '<script src="https://evil.test/x.js"></script>',
            'google_analytics_id' => 'not-an-id',
            'google_tag_manager_id' => 'GTM-',
        ])->assertSessionHasErrors(['google_site_verification', 'custom_meta_tags', 'google_analytics_id', 'google_tag_manager_id']);

        $this->get('/')->assertDontSee('evil.test', false);
    }

    public function test_staff_with_settings_permission_can_add_meta_tags_but_not_scripts(): void
    {
        $role = Role::query()->create(['name' => 'Settings editor', 'slug' => 'settings-editor']);
        $role->permissions()->sync(Permission::query()->where('slug', 'settings')->pluck('id'));
        $editor = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $this->actingAs($editor)->put('/admin/settings/verification', [
            'custom_meta_tags' => '<meta name="norton-safeweb-site-verification" content="norton1" onload="alert(1)">',
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertSee('<meta name="norton-safeweb-site-verification" content="norton1">', false)->assertDontSee('onload="alert', false);
    }

    public function test_tag_parser_handles_quotes_order_and_entities(): void
    {
        $this->assertSame('abc', VerificationTags::code('abc'));
        $this->assertSame('abc', VerificationTags::code(' "abc" '));
        $this->assertSame('a+b/c=', VerificationTags::code('<META CONTENT=a+b/c= NAME=x>'));
        $this->assertNull(VerificationTags::code('has spaces in it'));
        $this->assertNull(VerificationTags::code(''));

        $this->assertSame(
            [['attribute' => 'property', 'name' => 'fb:app_id', 'content' => 'R&D "1"']],
            VerificationTags::parse('<meta property="fb:app_id" content="R&amp;D &quot;1&quot;">'),
        );
        $this->assertSame([], VerificationTags::parse('<meta property="og:title" content="x"><meta name="viewport" content="x">'));
        $this->assertSame('<meta name="x" content="&lt;b&gt;">', VerificationTags::normalise('<meta name="x" content="&lt;b&gt;">'));
    }
}
