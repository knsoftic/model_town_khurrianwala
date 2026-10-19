<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $order = 0;
        foreach (Permission::DEFINITIONS as $slug => [$name, $description]) {
            Permission::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'sort_order' => $order++]);
        }

        $roles = [
            Role::SUPER_ADMIN => ['Super Admin', 'Full access to everything, including users and scripts.', true, []],
            'content-manager' => ['Content Manager', 'Manages website content, plots, gallery and testimonials.', false,
                ['dashboard', 'plots', 'installments', 'testimonials', 'gallery', 'locations', 'cms']],
            'sales-manager' => ['Sales Manager', 'Handles leads, booking requests and sales reports.', false,
                ['dashboard', 'plots', 'installments', 'leads', 'bookings', 'reports']],
            'support-staff' => ['Support Staff', 'Responds to leads and contact messages.', false,
                ['dashboard', 'leads', 'bookings']],
        ];

        foreach ($roles as $slug => [$name, $description, $isSuper, $permissions]) {
            $role = Role::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'is_super' => $isSuper]);
            $role->permissions()->sync(Permission::query()->whereIn('slug', $permissions)->pluck('id'));
        }
    }
}
