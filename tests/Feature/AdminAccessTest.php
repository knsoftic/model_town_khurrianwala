<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSite();
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/plots')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_staff_can_sign_in_and_inactive_staff_cannot(): void
    {
        $user = $this->staff('sales-manager', ['email' => 'sales@test.local', 'password' => 'Secret-Pass-123']);

        $this->post('/admin/login', ['email' => 'sales@test.local', 'password' => 'Secret-Pass-123'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->post('/admin/logout');

        $user->update(['is_active' => false]);
        $this->post('/admin/login', ['email' => 'sales@test.local', 'password' => 'Secret-Pass-123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_locked_after_repeated_failures(): void
    {
        $this->staff('sales-manager', ['email' => 'sales@test.local', 'password' => 'Secret-Pass-123']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'sales@test.local', 'password' => 'wrong']);
        }

        $this->post('/admin/login', ['email' => 'sales@test.local', 'password' => 'Secret-Pass-123'])
            ->assertSessionHasErrors(['email' => 'Too many sign-in attempts. Please try again in 5 minute(s).']);
    }

    public function test_roles_limit_what_staff_can_open(): void
    {
        $support = $this->staff('support-staff');
        $this->actingAs($support)->get('/admin/leads')->assertOk();
        $this->actingAs($support)->get('/admin/bookings')->assertOk();
        $this->actingAs($support)->get('/admin/plots')->assertForbidden();
        $this->actingAs($support)->get('/admin/settings/general')->assertForbidden();
        $this->actingAs($support)->get('/admin/users')->assertForbidden();
        $this->actingAs($support)->patchJson('/admin/toggle/plots/1', ['field' => 'is_active', 'value' => false])->assertForbidden();

        $content = $this->staff('content-manager');
        $this->actingAs($content)->get('/admin/plots')->assertOk();
        $this->actingAs($content)->get('/admin/sections')->assertOk();
        $this->actingAs($content)->get('/admin/leads')->assertForbidden();
        $this->actingAs($content)->get('/admin/reports')->assertForbidden();
    }

    public function test_user_without_dashboard_permission_lands_on_first_allowed_page(): void
    {
        $role = Role::query()->create(['name' => 'Gallery only', 'slug' => 'gallery-only']);
        $role->permissions()->sync(Permission::query()->where('slug', 'gallery')->pluck('id'));
        $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.gallery.index'));
    }

    public function test_super_admin_cannot_delete_themselves_or_the_last_super_admin(): void
    {
        $super = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();

        $this->actingAs($super)->delete("/admin/users/{$super->id}")->assertSessionHas('error');
        $this->assertNotSoftDeleted($super);

        $other = $this->staff('super-admin');
        $this->actingAs($other)->delete("/admin/users/{$super->id}")->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted($super);

        $this->actingAs($other)->delete("/admin/users/{$other->id}")->assertSessionHas('error');
    }

    public function test_non_super_admin_cannot_edit_super_admin_or_grant_super_role(): void
    {
        $role = Role::query()->create(['name' => 'User manager', 'slug' => 'user-manager']);
        $role->permissions()->sync(Permission::query()->where('slug', 'users')->pluck('id'));
        $manager = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $super = User::query()->whereHas('role', fn ($q) => $q->where('is_super', true))->firstOrFail();

        $this->actingAs($manager)->get("/admin/users/{$super->id}/edit")->assertForbidden();
        $this->actingAs($manager)->post('/admin/users', [
            'name' => 'Sneaky', 'email' => 'sneaky@test.local', 'role_id' => Role::query()->where('is_super', true)->value('id'),
            'password' => 'Secret-Pass-123', 'password_confirmation' => 'Secret-Pass-123', 'is_active' => '1',
        ])->assertSessionHasErrors('role_id');
    }

    public function test_custom_scripts_are_super_admin_only(): void
    {
        $role = Role::query()->create(['name' => 'Settings editor', 'slug' => 'settings-editor']);
        $role->permissions()->sync(Permission::query()->where('slug', 'settings')->pluck('id'));
        $editor = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $this->actingAs($editor)->put('/admin/settings/seo', [
            'seo_title' => 'New title', 'footer_scripts' => '<script>alert(1)</script>',
        ])->assertRedirect();

        $this->assertSame('New title', setting('seo_title'));
        $this->assertSame('', (string) setting('footer_scripts', ''));
    }
}
