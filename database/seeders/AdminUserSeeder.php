<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * The Super Admin login comes from SEED_ADMIN_EMAIL / SEED_ADMIN_PASSWORD in .env.
     * Outside production, two demo staff accounts (random, unknown passwords) are added so leads can be assigned.
     */
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'slug');

        $password = env('SEED_ADMIN_PASSWORD');

        if (blank($password)) {
            $password = Str::password(16);
            $this->command?->warn("SEED_ADMIN_PASSWORD is not set. Generated Super Admin password: {$password}");
        }

        User::query()->updateOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@example.com')],
            ['name' => 'Super Admin', 'password' => $password, 'role_id' => $roles[Role::SUPER_ADMIN], 'is_active' => true, 'email_verified_at' => now()],
        );

        if (app()->isProduction()) {
            return;
        }

        $staff = [
            ['Ali Raza (Demo)', 'sales.demo@example.com', 'sales-manager'],
            ['Sana Malik (Demo)', 'support.demo@example.com', 'support-staff'],
        ];

        foreach ($staff as [$name, $email, $role]) {
            User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Str::password(24), 'role_id' => $roles[$role], 'is_active' => true],
            );
        }
    }
}
