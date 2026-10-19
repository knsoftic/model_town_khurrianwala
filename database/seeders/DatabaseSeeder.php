<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SiteContentSeeder::class,
            PlotSeeder::class,
            ProjectContentSeeder::class,
            ProjectDataSeeder::class,
            PaymentPlanSeeder::class,
        ]);

        if (! app()->isProduction()) {
            $this->call(DemoCrmSeeder::class);
        }
    }
}
