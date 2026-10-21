<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\Location;
use App\Models\Page;
use App\Models\Plot;
use App\Models\ProjectFeature;
use App\Models\Statistic;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

/**
 * The real project details for Model Town Khurrianwala (location, contact numbers,
 * amenities and website copy; SEO text is in SeoSeeder). Safe to run again: it updates content in place and
 * never touches users, leads, bookings, plot prices or uploaded images.
 *
 *   php artisan db:seed --class=ProjectDataSeeder --force
 */
class ProjectDataSeeder extends Seeder
{
    public const LATITUDE = 31.510134;

    public const LONGITUDE = 73.257767;

    public const PHONE = '+92 300 8653838';

    public const PHONE_SECONDARY = '+92 308 8653838';

    public const ADDRESS = 'Main Bazar Khurrianwala, Jhumra Road, Faisalabad, Punjab, Pakistan';

    public const MAPS_URL = 'https://maps.google.com/?q=31.510134,73.257767';

    public const MAP_EMBED = 'https://www.google.com/maps?q=31.510134,73.257767&z=16&output=embed';

    /**
     * Title, icon, description — in the order shown on the website.
     *
     * @var array<int, array{0: string, 1: string, 2: string}>
     */
    public const FEATURES = [
        ['Prime Location', 'map-pin', 'On Main Bazar Khurrianwala, Jhumra Road — between the Expressway and GT Road.'],
        ['Closest to the Motorway', 'car-front', 'The nearest housing society to the Motorway, for easy travel in every direction.'],
        ['50 ft & 30 ft Wide Roads', 'road', 'Wide 50 ft main roads and 30 ft streets across the society.'],
        ['Beautiful Parks', 'trees', 'Landscaped parks for families, children and evening walks.'],
        ['Green Belts', 'sprout', 'Green belts that keep the community fresh, clean and open.'],
        ['Gated Community', 'fence', 'A gated community with controlled entry for peace of mind.'],
        ['24/7 Security', 'shield-check', 'Round-the-clock security for residents and their homes.'],
        ['Boundary Wall', 'brick-wall', 'A boundary wall around the entire society.'],
        ['Mosque', 'mosque', 'A mosque inside the society for daily and Friday prayers.'],
        ['Electricity', 'zap', 'Electricity supply for every home in the society.'],
        ['Gas', 'flame', 'Gas supply for homes.'],
        ['Mineral Water Supply', 'glass-water', 'Clean mineral water supply for residents.'],
        ['Sewerage System', 'waves-horizontal', 'A planned sewerage system serving every street.'],
        ['Solid Waste Management', 'trash', 'Regular solid waste collection to keep the society clean.'],
        ['Public Buildings', 'building', 'Public buildings planned to serve the whole community.'],
        ['Ideal for Living & Investment', 'trending-up', 'A sound choice whether you are building your home or investing for the future.'],
    ];

    public function run(SettingsService $settings): void
    {
        $this->settings($settings);
        $this->location();
        $this->features();
        $this->statistics();
        $this->homepage();
        $this->pages();
        $this->faqs();
        $this->plots();
    }

    protected function settings(SettingsService $settings): void
    {
        $settings->update('general', [
            'site_name' => 'Model Town Khurrianwala',
            'site_tagline' => 'Gated Community on Jhumra Road, Khurrianwala — Ideal for Living & Investment',
            'logo_subtitle' => 'Khurrianwala · Faisalabad',
            'demo_notice_text' => 'Testimonials and photos shown are sample content. Call 0300-8653838 for more information.',
        ]);

        $settings->update('contact', [
            'phone' => self::PHONE,
            'phone_secondary' => self::PHONE_SECONDARY,
            'whatsapp_number' => self::PHONE,
            // The demo addresses are removed; add the real email under Settings → Contact Information.
            'email' => '',
            'email_secondary' => '',
            'office_address' => self::ADDRESS,
            'google_maps_url' => self::MAPS_URL,
            'google_maps_embed' => self::MAP_EMBED,
        ]);

        $settings->update('header', [
            'topbar_text' => 'Bookings open — Main Bazar Khurrianwala, Jhumra Road',
        ]);

        $settings->update('footer', [
            'footer_description' => 'A gated community on Main Bazar Khurrianwala, Jhumra Road, Faisalabad — with 50 ft and 30 ft wide roads, parks, mosque, gas, electricity, mineral water and 24/7 security.',
        ]);
    }

    protected function location(): void
    {
        Location::query()->updateOrCreate(['slug' => 'khurrianwala-main-site'], [
            'name' => 'Model Town Khurrianwala, Faisalabad',
            'city' => 'Faisalabad',
            'area' => 'Khurrianwala',
            'address' => self::ADDRESS,
            'short_description' => 'On Main Bazar Khurrianwala, Jhumra Road — between the Expressway and GT Road, and the closest society to the Motorway.',
            'description' => '<p>Model Town Khurrianwala is located on Main Bazar Khurrianwala, Jhumra Road, Faisalabad — between the Expressway and GT Road, and the closest housing society to the Motorway.</p>'
                .'<p>Main Bazar puts shops and everyday needs at your doorstep, while the Expressway, GT Road and Motorway connect you quickly to Faisalabad city and beyond.</p>',
            'latitude' => self::LATITUDE,
            'longitude' => self::LONGITUDE,
            'google_maps_url' => self::MAPS_URL,
            'map_embed_url' => self::MAP_EMBED,
            'landmarks' => [
                ['name' => 'Main Bazar Khurrianwala', 'distance' => 'At the doorstep'],
                ['name' => 'Jhumra Road', 'distance' => 'On the road'],
                ['name' => 'Expressway', 'distance' => 'Close by'],
                ['name' => 'GT Road', 'distance' => 'Close by'],
                ['name' => 'Motorway', 'distance' => 'Closest society'],
            ],
            'distance_airport' => null,
            'distance_motorway' => 'Closest society to the Motorway',
            'distance_main_road' => 'On Jhumra Road',
            'is_featured' => true,
            'is_active' => true,
        ]);
    }

    /**
     * The amenities from the project brochure. Features that are not on this list are
     * switched off (not deleted), so anything added in the admin can be turned back on.
     */
    protected function features(): void
    {
        $titles = [];

        foreach (self::FEATURES as $i => [$title, $icon, $description]) {
            ProjectFeature::query()->updateOrCreate(['title' => $title], ['icon' => $icon, 'description' => $description, 'sort_order' => $i, 'is_active' => true]);
            $titles[] = $title;
        }

        ProjectFeature::query()->whereNotIn('title', $titles)->update(['is_active' => false]);
    }

    /**
     * Real facts instead of the demo figures ("10+ years", "5,000+ clients").
     */
    protected function statistics(): void
    {
        $facts = [
            ['Wide Main Roads', '50', ' ft', 'road'],
            ['Wide Streets', '30', ' ft', 'route'],
            ['Security', '24/7', null, 'shield-check'],
            ['Community Amenities', '13', null, 'badge-check'],
        ];

        foreach (['intro', 'about'] as $group) {
            $labels = [];

            foreach ($facts as $i => [$label, $value, $suffix, $icon]) {
                Statistic::query()->updateOrCreate(
                    ['group' => $group, 'label' => $label],
                    ['value' => $value, 'prefix' => null, 'suffix' => $suffix, 'icon' => $icon, 'sort_order' => $i, 'is_active' => true],
                );
                $labels[] = $label;
            }

            Statistic::query()->where('group', $group)->whereNotIn('label', $labels)->update(['is_active' => false]);
        }
    }

    protected function homepage(): void
    {
        $this->section('hero', [
            'heading' => 'Own Your Plot in Model Town Khurrianwala',
            'subheading' => 'Main Bazar Khurrianwala, Jhumra Road — between the Expressway and GT Road.',
            'description' => 'A gated community with 50 ft and 30 ft wide roads, parks, mosque, gas, electricity, mineral water and 24/7 security. Ideal for living and investment.',
        ], ['badge_text' => 'Bookings Open · Closest to the Motorway']);

        $this->section('intro', [
            'eyebrow' => 'About The Project',
            'heading' => 'Ideal for Living and Investment',
            'description' => 'Model Town Khurrianwala is a gated community on Main Bazar Khurrianwala, Jhumra Road, Faisalabad. It sits between the Expressway and GT Road and is the closest society to the Motorway — with 50 ft and 30 ft wide roads, beautiful parks, green belts, a mosque, gas, electricity, mineral water, a sewerage system, solid waste management, a boundary wall and 24/7 security.',
        ]);

        $this->section('features', [
            'eyebrow' => 'Project Features',
            'heading' => 'Everything Your Family Needs',
            'subheading' => 'Wide roads, parks, utilities and round-the-clock security — all inside a gated community.',
        ], ['limit' => count(self::FEATURES)]);

        $this->section('location', [
            'eyebrow' => 'Prime Location',
            'heading' => 'On Main Bazar, Jhumra Road',
            'subheading' => 'Between the Expressway and GT Road — and the closest society to the Motorway.',
        ]);

        $this->section('contact_cta', [
            'subheading' => 'Call 0300-8653838 or 0308-8653838, message us on WhatsApp or send an inquiry. Our team will share the latest prices and availability.',
        ]);
    }

    protected function pages(): void
    {
        $this->page('about', [
            'eyebrow' => 'About The Project',
            'heading' => 'Model Town Khurrianwala, Faisalabad',
            'subheading' => 'A gated community on Main Bazar Khurrianwala, Jhumra Road — ideal for living and investment.',
            'content' => '<p>Model Town Khurrianwala is a gated residential community on Main Bazar Khurrianwala, Jhumra Road, Faisalabad. It is located between the Expressway and GT Road and is the closest housing society to the Motorway.</p>'
                .'<h3>Location</h3><ul><li>Main Bazar Khurrianwala, Jhumra Road</li><li>Between the Expressway and GT Road</li><li>Closest society to the Motorway</li></ul>'
                .'<h3>Amenities</h3><ul><li>50 ft and 30 ft wide roads</li><li>Beautiful parks and green belts</li><li>Gated community with a boundary wall</li><li>24/7 security</li><li>Mosque</li><li>Electricity and gas</li><li>Mineral water supply</li><li>Sewerage system</li><li>Solid waste management</li><li>Public buildings</li></ul>'
                .'<p>Ideal for building your home and for investment. For more information, call <a href="tel:+923008653838">0300-8653838</a> or <a href="tel:+923088653838">0308-8653838</a>.</p>',
        ]);

        $this->page('locations', [
            'heading' => 'Main Bazar Khurrianwala, Jhumra Road',
            'subheading' => 'Between the Expressway and GT Road — the closest society to the Motorway.',
        ]);

        $this->page('contact', [
            'subheading' => 'Call 0300-8653838 or 0308-8653838, send a WhatsApp message, or visit us on Main Bazar Khurrianwala, Jhumra Road.',
        ]);
    }

    /**
     * Location, amenities and contact questions first, then the existing booking and payment ones.
     */
    protected function faqs(): void
    {
        $new = [
            ['General', 'Where is Model Town Khurrianwala located?', 'Model Town Khurrianwala is on Main Bazar Khurrianwala, Jhumra Road, Faisalabad — between the Expressway and GT Road, and the closest society to the Motorway. Open the Location page for the map and directions.'],
            ['General', 'Which amenities are available in the society?', '50 ft and 30 ft wide roads, beautiful parks and green belts, a gated community with a boundary wall, 24/7 security, a mosque, electricity, gas, mineral water supply, a sewerage system, solid waste management and public buildings.'],
            ['General', 'How can I contact the sales team?', 'Call or WhatsApp 0300-8653838, or call 0308-8653838. You can also send an inquiry from the Contact page and our team will call you back.'],
        ];

        foreach ($new as $i => [$category, $question, $answer]) {
            Faq::query()->updateOrCreate(['question' => $question], ['category' => $category, 'answer' => $answer, 'sort_order' => $i, 'is_active' => true]);
        }

        Faq::query()->where('question', 'Can I visit the project site?')->update([
            'answer' => 'Yes. Call 0300-8653838 to schedule a site visit to Main Bazar Khurrianwala, Jhumra Road — our team will show you the society and the available plots.',
        ]);

        $offset = count($new);
        Faq::query()->whereNotIn('question', array_column($new, 1))->orderBy('sort_order')->orderBy('id')->get()
            ->each(function (Faq $faq) use (&$offset) {
                $faq->update(['sort_order' => $offset++]);
            });
    }

    /**
     * Swap the generic amenity line in the demo plot descriptions for the real one.
     */
    protected function plots(): void
    {
        Plot::query()->where('full_description', 'like', '%access to parks and the commercial area%')->get()
            ->each(fn (Plot $plot) => $plot->update([
                'full_description' => str_replace(
                    'Located on planned streets with access to parks and the commercial area',
                    'Located in a gated community with 50 ft and 30 ft roads, parks and 24/7 security',
                    $plot->full_description,
                ),
            ]));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $settings
     */
    protected function section(string $key, array $attributes, array $settings = []): void
    {
        $section = HomepageSection::query()->where('key', $key)->first();

        $section?->update($attributes + ['settings' => array_merge((array) $section->settings, $settings)]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function page(string $slug, array $attributes): void
    {
        $page = Page::query()->where('slug', $slug)->first();

        if ($page) {
            $page->update($attributes);
        }
    }
}
