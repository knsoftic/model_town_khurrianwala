<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PlotSeeder;
use Database\Seeders\ProjectContentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed roles, settings, pages, sections, plots and content (no demo CRM data).
     */
    protected function seedSite(): void
    {
        Storage::fake('public');

        $this->seed([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SiteContentSeeder::class,
            PlotSeeder::class,
            ProjectContentSeeder::class,
        ]);
    }

    protected function staff(string $roleSlug, array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role_id' => Role::query()->where('slug', $roleSlug)->value('id'),
            'is_active' => true,
        ]);
    }
}
