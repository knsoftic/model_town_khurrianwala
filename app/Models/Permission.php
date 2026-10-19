<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'description', 'sort_order'])]
class Permission extends Model
{
    /**
     * Every permission the admin panel checks, in menu order.
     * Super Admins implicitly have all of them.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const DEFINITIONS = [
        'dashboard' => ['Dashboard', 'View the dashboard, statistics and charts'],
        'plots' => ['Plots', 'Manage plot sizes, prices, availability and featured plots'],
        'installments' => ['Installments', 'Manage installment plans and payment schedules'],
        'leads' => ['Leads', 'View and manage leads, contact messages and callbacks'],
        'bookings' => ['Bookings', 'Review and manage plot booking requests'],
        'testimonials' => ['Testimonials', 'Manage happy client testimonials'],
        'gallery' => ['Gallery', 'Manage gallery albums, images and videos'],
        'locations' => ['Locations', 'Manage project locations and maps'],
        'cms' => ['CMS', 'Manage homepage sections, pages, menus, features, development, FAQs and footer'],
        'settings' => ['Settings', 'Manage website settings, appearance, SEO and notifications'],
        'users' => ['Users', 'Manage admin users, roles and view activity logs'],
        'reports' => ['Reports', 'View and export reports'],
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
