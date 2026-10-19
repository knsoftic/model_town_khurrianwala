<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Statistic;
use App\Services\SettingsService;
use Database\Seeders\Support\DemoArt;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(SettingsService $settings): void
    {
        $settings->seedDefaults();

        $hero = DemoArt::save('hero-master-plan', DemoArt::masterPlan(1920, 1080, true, 7));
        $banner = DemoArt::save('page-banner', DemoArt::masterPlan(1920, 640, true, 21));
        $intro = DemoArt::save('intro-master-plan', DemoArt::masterPlan(1200, 900, false, 11));
        $about = DemoArt::save('about-site-gate', DemoArt::scene('project', 1, 1200, 900));
        $og = DemoArt::save('og-default', DemoArt::masterPlan(1200, 630, true, 3));

        Setting::query()->where('key', 'og_image')->update(['value' => $og]);
        $settings->flush();

        $this->seedNavigation();
        $this->seedPages($banner, $about);
        $this->seedHomepage($hero, $intro);
        $this->seedStatistics();
        $this->seedSocialLinks();
    }

    protected function seedNavigation(): void
    {
        $header = ['home', 'about', 'plots.index', 'installment-plans', 'locations.index', 'development', 'gallery', 'testimonials', 'contact'];

        foreach ($header as $i => $route) {
            NavigationItem::query()->updateOrCreate(
                ['location' => 'header', 'route_name' => $route],
                ['label' => NavigationItem::ROUTES[$route][0], 'sort_order' => $i, 'is_active' => true, 'is_system' => true],
            );
        }

        $quick = ['home', 'about', 'plots.index', 'installment-plans', 'locations.index', 'development', 'gallery'];

        foreach ($quick as $i => $route) {
            NavigationItem::query()->updateOrCreate(
                ['location' => 'footer_quick', 'route_name' => $route],
                ['label' => NavigationItem::ROUTES[$route][0], 'sort_order' => $i, 'is_active' => true],
            );
        }

        $useful = [
            ['3 Marla Plots', '/plots/3-marla'],
            ['5 Marla Plots', '/plots/5-marla'],
            ['10 Marla Plots', '/plots/10-marla'],
            ['1 Kanal Plots', '/plots/1-kanal'],
            ['Book Your Plot', null, 'book-plot'],
            ['FAQs', null, 'faq'],
            ['Happy Clients', null, 'testimonials'],
            ['Contact Us', null, 'contact'],
        ];

        foreach ($useful as $i => $item) {
            NavigationItem::query()->updateOrCreate(
                ['location' => 'footer_useful', 'label' => $item[0]],
                ['url' => $item[1], 'route_name' => $item[2] ?? null, 'sort_order' => $i, 'is_active' => true],
            );
        }
    }

    protected function seedPages(string $banner, string $about): void
    {
        $pages = [
            'home' => ['name' => 'Home', 'heading' => null, 'subheading' => null],
            'about' => [
                'name' => 'About Project',
                'eyebrow' => 'About The Project',
                'heading' => 'A Planned Community Built on Trust',
                'subheading' => 'Transparent pricing, flexible installments and visible on-ground development.',
                'image' => $about,
                'content' => '<p>Our project brings together well-planned residential and commercial plots, wide roads, green spaces and everyday conveniences — designed for families who want to own land with confidence.</p><p>Every plot size comes with a clear, published installment plan. You always know the total price, what is due at booking, the monthly installment and every milestone payment until possession.</p><h3>Why families choose us</h3><ul><li>Clear payment schedules with no hidden charges</li><li>Multiple plot sizes for every budget</li><li>Regular development progress updates</li><li>A dedicated sales and support team</li></ul><p><em>This is demo content. Replace it from Admin → CMS → Pages → About Project.</em></p>',
                'cta_text' => 'View Plot Sizes',
                'cta_url' => '/plots',
                'settings' => ['show_statistics' => true, 'show_features' => true, 'show_video' => true],
            ],
            'plots' => ['name' => 'Plot Sizes', 'eyebrow' => 'Plot Sizes', 'heading' => 'Choose the Plot That Fits Your Plans', 'subheading' => 'Residential plots in 3 Marla, 5 Marla, 10 Marla and 1 Kanal — each with a published installment plan.'],
            'installment-plans' => ['name' => 'Installment Plans', 'eyebrow' => 'Installment Plans', 'heading' => 'Easy Installment Plans, Clearly Explained', 'subheading' => 'See exactly what you pay at booking, every month, at each milestone and at possession.'],
            'locations' => ['name' => 'Locations', 'eyebrow' => 'Prime Location', 'heading' => 'Well Connected, Thoughtfully Located', 'subheading' => 'Close to main roads, the city and everyday essentials.'],
            'development' => ['name' => 'Development', 'eyebrow' => 'Development Status', 'heading' => 'Development You Can See', 'subheading' => 'Track on-ground progress across roads, utilities, parks and the commercial area.'],
            'gallery' => ['name' => 'Gallery', 'eyebrow' => 'Project Gallery', 'heading' => 'See the Project Take Shape', 'subheading' => 'Photos from the site, development work, events and site visits.'],
            'testimonials' => ['name' => 'Testimonials', 'eyebrow' => 'Happy Clients', 'heading' => 'What Our Clients Say', 'subheading' => 'Experiences shared by families who booked their plots with us.'],
            'faq' => ['name' => 'FAQ', 'eyebrow' => 'FAQs', 'heading' => 'Frequently Asked Questions', 'subheading' => 'Answers about booking, installments, documents and possession.'],
            'contact' => ['name' => 'Contact', 'eyebrow' => 'Contact Us', 'heading' => 'Talk to Our Sales Team', 'subheading' => 'Visit our office, call, WhatsApp or send a message — we are happy to help.', 'settings' => ['show_map' => true, 'show_callback' => true]],
            'book-plot' => ['name' => 'Book Your Plot', 'eyebrow' => 'Book Your Plot', 'heading' => 'Request Your Plot Booking', 'subheading' => 'Choose a plot size and installment plan. Our team will contact you to confirm availability and guide you through booking.'],
        ];

        foreach ($pages as $slug => $data) {
            $page = Page::query()->updateOrCreate(['slug' => $slug], $data + ['banner_image' => $banner, 'is_active' => true]);

            if ($data['heading']) {
                $page->seo()->updateOrCreate([], [
                    'title' => $data['heading'],
                    'description' => $data['subheading'],
                ]);
            }
        }
    }

    protected function seedHomepage(string $hero, string $intro): void
    {
        $sections = [
            ['hero', 'hero', null, 'Secure Your Future With Your Own Plot', 'Flexible installment plans. Prime locations. Trusted development.',
                'Choose from 3 Marla, 5 Marla, 10 Marla and 1 Kanal plots with transparent prices and easy monthly installments.', [
                    'background_type' => 'image', 'background_image' => $hero, 'overlay_opacity' => 55, 'height' => 92, 'text_alignment' => 'left',
                    'badge_text' => 'Bookings Open · Limited Plots',
                    'primary_button_text' => 'Explore Plot Plans', 'primary_button_url' => '/plots',
                    'secondary_button_text' => 'Book Now', 'secondary_button_url' => '/book-plot',
                    'show_quick_facts' => true,
                ]],
            ['intro', 'intro', 'Your Future Starts Here', 'Own Land in a Community Planned for Living', null,
                'We offer residential and commercial plots with clear ownership, transparent payment plans and steady development. Whether you are buying your first plot or investing for the future, our team guides you at every step — from choosing the right size to possession.', [
                    'image' => $intro, 'button_text' => 'About The Project', 'button_url' => '/about', 'show_statistics' => true,
                ]],
            ['plots', 'plots', 'Plot Sizes', 'Available Plot Sizes', 'Pick a size, see the price and the complete installment plan.', null, [
                'limit' => 8, 'featured_only' => false, 'button_text' => 'View All Plot Sizes', 'button_url' => '/plots',
            ]],
            ['installment_plans', 'installment_plans', 'Payment Schedule', 'Know Every Payment Before You Book', 'A clear, step-by-step schedule for each plot size.', null, [
                'button_text' => 'Compare All Plans', 'button_url' => '/installment-plans',
            ]],
            ['calculator', 'calculator', 'Payment Calculator', 'Calculate Your Payment Plan', 'Select a plot size to see the total price, booking amount, monthly installment and every milestone payment instantly.', null, []],
            ['features', 'features', 'Project Features', 'Everything a Modern Community Needs', 'Security, utilities, green spaces and convenience — planned from day one.', null, ['limit' => 12]],
            ['location', 'location', 'Prime Location', 'Located Where Life Connects', 'Easy access to main roads, the city and everyday essentials.', null, []],
            ['development', 'development', 'Development Status', 'Development Progress', 'Live progress of on-ground development work across the project.', null, ['button_text' => 'View Development Updates', 'button_url' => '/development']],
            ['testimonials', 'testimonials', 'Happy Clients', 'Trusted by Families Like Yours', 'What our clients say about booking and paying through our installment plans.', null, ['limit' => 6]],
            ['gallery', 'gallery', 'Project Gallery', 'A Look at the Project', 'Development work, events and site visits.', null, ['limit' => 8, 'button_text' => 'Open Full Gallery', 'button_url' => '/gallery']],
            ['video', 'video', 'Discover Our Project', 'Take a Video Tour', 'See the master plan, development work and surroundings.', null, []],
            ['faq', 'faq', 'FAQs', 'Questions, Answered', 'Everything you need to know before booking your plot.', null, ['limit' => 6, 'button_text' => 'View All FAQs', 'button_url' => '/faq']],
            ['contact_cta', 'contact_cta', 'Get Started', 'Ready to Book Your Plot?', 'Send an inquiry, request a callback or message us on WhatsApp. Our sales team will share the latest prices and availability.', null, [
                'show_form' => true, 'primary_button_text' => 'Book Your Plot', 'primary_button_url' => '/book-plot',
            ]],
        ];

        foreach ($sections as $i => [$key, $type, $eyebrow, $heading, $subheading, $description, $sectionSettings]) {
            HomepageSection::query()->updateOrCreate(['key' => $key], [
                'type' => $type,
                'name' => HomepageSection::TYPES[$type],
                'eyebrow' => $eyebrow,
                'heading' => $heading,
                'subheading' => $subheading,
                'description' => $description,
                'settings' => $sectionSettings,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => $i,
            ]);
        }
    }

    protected function seedStatistics(): void
    {
        $stats = [
            'intro' => [
                ['Years Experience', '10', '+', 'award'],
                ['Happy Clients', '5000', '+', 'smile'],
                ['Plots Sold', '1000', '+', 'land-plot'],
                ['Transparent Process', '100', '%', 'badge-check'],
            ],
            'about' => [
                ['Years Experience', '10', '+', 'award'],
                ['Plots Delivered', '1000', '+', 'land-plot'],
                ['Plot Categories', '4', null, 'layout-grid'],
                ['Transparent Process', '100', '%', 'badge-check'],
            ],
        ];

        foreach ($stats as $group => $items) {
            foreach ($items as $i => [$label, $value, $suffix, $icon]) {
                Statistic::query()->updateOrCreate(
                    ['group' => $group, 'label' => $label],
                    ['value' => $value, 'suffix' => $suffix, 'icon' => $icon, 'sort_order' => $i, 'is_active' => true],
                );
            }
        }
    }

    protected function seedSocialLinks(): void
    {
        $links = ['facebook' => 'https://www.facebook.com/', 'instagram' => 'https://www.instagram.com/', 'youtube' => 'https://www.youtube.com/', 'tiktok' => 'https://www.tiktok.com/', 'linkedin' => 'https://www.linkedin.com/'];

        $i = 0;
        foreach ($links as $platform => $url) {
            SocialLink::query()->updateOrCreate(['platform' => $platform], ['url' => $url, 'sort_order' => $i++, 'is_active' => true]);
        }
    }
}
