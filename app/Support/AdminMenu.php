<?php

namespace App\Support;

use App\Models\User;

/**
 * Admin sidebar definition. Items are hidden when the user lacks the permission.
 */
final class AdminMenu
{
    /**
     * @param  array<string, int>  $badges
     * @return array<int, array{label: string|null, items: array<int, array<string, mixed>>}>
     */
    public static function for(User $user, array $badges = []): array
    {
        $groups = [
            [null, [
                ['Dashboard', 'layout-dashboard', 'admin.dashboard', [], 'dashboard', ['admin.dashboard']],
            ]],
            ['Sales & CRM', [
                ['Leads', 'users-round', 'admin.leads.index', [], 'leads', ['admin.leads.*'], 'leads'],
                ['Booking Requests', 'bookmark-check', 'admin.bookings.index', [], 'bookings', ['admin.bookings.*'], 'bookings'],
                ['Contact Messages', 'inbox', 'admin.messages.index', [], 'leads', ['admin.messages.*'], 'messages'],
                ['Subscribers', 'mail-plus', 'admin.subscribers.index', [], 'leads', ['admin.subscribers.*']],
            ]],
            ['Plots', [
                ['All Plot Sizes', 'land-plot', 'admin.plots.index', [], 'plots', ['admin.plots.index', 'admin.plots.edit']],
                ['Add Plot Size', 'circle-plus', 'admin.plots.create', [], 'plots', ['admin.plots.create']],
                ['Installment Plans', 'calendar-range', 'admin.installment-plans.index', [], 'installments', ['admin.installment-plans.index', 'admin.installment-plans.create', 'admin.installment-plans.edit']],
                ['Payment Schedules', 'list-ordered', 'admin.installment-plans.schedules', [], 'installments', ['admin.installment-plans.schedules']],
                ['Availability', 'grid-3x3', 'admin.plots.availability', [], 'plots', ['admin.plots.availability']],
                ['Featured Plots', 'star', 'admin.plots.featured', [], 'plots', ['admin.plots.featured']],
            ]],
            ['Website Content', [
                ['Homepage Sections', 'layout-template', 'admin.sections.index', [], 'cms', ['admin.sections.*']],
                ['Pages', 'file-text', 'admin.pages.index', [], 'cms', ['admin.pages.*']],
                ['Menus', 'menu', 'admin.menus.index', [], 'cms', ['admin.menus.*']],
                ['Project Features', 'sparkles', 'admin.features.index', [], 'cms', ['admin.features.*']],
                ['Development Progress', 'hard-hat', 'admin.development.index', [], 'cms', ['admin.development.*']],
                ['Statistics', 'chart-no-axes-column', 'admin.statistics.index', [], 'cms', ['admin.statistics.*']],
                ['FAQs', 'circle-question-mark', 'admin.faqs.index', [], 'cms', ['admin.faqs.*']],
                ['Locations', 'map-pin', 'admin.locations.index', [], 'locations', ['admin.locations.*']],
                ['Testimonials', 'quote', 'admin.testimonials.index', [], 'testimonials', ['admin.testimonials.*']],
                ['Gallery', 'images', 'admin.gallery.index', [], 'gallery', ['admin.gallery.*']],
                ['Videos', 'clapperboard', 'admin.videos.index', [], 'gallery', ['admin.videos.*']],
            ]],
            ['Settings', [
                ['Website Settings', 'settings', 'admin.settings.edit', ['group' => 'general'], 'settings', ['admin.settings.*'], null, fn () => request()->route('group') !== 'appearance'],
                ['Appearance', 'palette', 'admin.settings.edit', ['group' => 'appearance'], 'settings', ['admin.settings.*'], null, fn () => request()->route('group') === 'appearance'],
                ['Social Links', 'share-2', 'admin.social-links.index', [], 'settings', ['admin.social-links.*']],
            ]],
            ['Administration', [
                ['Reports', 'chart-pie', 'admin.reports.index', [], 'reports', ['admin.reports.*']],
                ['Users', 'user-cog', 'admin.users.index', [], 'users', ['admin.users.*']],
                ['Roles & Permissions', 'shield-check', 'admin.roles.index', [], 'users', ['admin.roles.*']],
                ['Activity Log', 'activity', 'admin.activity.index', [], 'users', ['admin.activity.*']],
            ]],
        ];

        $menu = [];

        foreach ($groups as [$label, $items]) {
            $visible = [];

            foreach ($items as $item) {
                [$text, $icon, $route, $params, $permission, $patterns] = $item;
                $badge = $item[6] ?? null;
                $extra = $item[7] ?? null;

                if (! $user->can($permission)) {
                    continue;
                }

                $visible[] = [
                    'label' => $text,
                    'icon' => $icon,
                    'url' => route($route, $params),
                    'active' => request()->routeIs(...$patterns) && ($extra === null || $extra()),
                    'badge' => $badge ? ($badges[$badge] ?? 0) : 0,
                ];
            }

            if ($visible !== []) {
                $menu[] = ['label' => $label, 'items' => $visible];
            }
        }

        return $menu;
    }
}
