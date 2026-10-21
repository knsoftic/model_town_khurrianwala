<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Page;
use App\Models\Plot;
use App\Services\SettingsService;
use App\Support\Keywords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

/**
 * On-page SEO for Model Town Khurrianwala: the website title, description and keywords, and a
 * title, description and keywords for every page, plot and location.
 *
 * Safe to run on the live website: it fills empty fields and replaces only the text the
 * earlier seeders wrote (page headings, old defaults). Anything changed in the admin panel
 * is kept. DatabaseSeeder runs it with force: true for a fresh install.
 *
 *   php artisan db:seed --class=SeoSeeder --force
 */
class SeoSeeder extends Seeder
{
    public const TITLE = 'Model Town Khurrianwala – Plots on Installments Faisalabad';

    public const DESCRIPTION = 'Buy 3, 5 & 10 Marla plots on easy installments at Model Town Khurrianwala, Jhumra Road, Faisalabad. Gated society near the Motorway. Call 0300-8653838.';

    /**
     * Website keywords: what people in Faisalabad type into Google when looking for a plot.
     *
     * @var array<int, string>
     */
    public const KEYWORDS = [
        'Model Town Khurrianwala',
        'Model Town Khurianwala',
        'Model Town Khurrianwala Faisalabad',
        'plots for sale in Faisalabad',
        'plots on installments in Faisalabad',
        'plot on installment Faisalabad',
        '3 marla plot for sale in Faisalabad',
        '5 marla plot for sale in Faisalabad',
        '10 marla plot for sale in Faisalabad',
        '5 marla plot on installments Faisalabad',
        'plots for sale in Khurrianwala',
        'Khurrianwala plots',
        'Jhumra Road plots',
        'plots on Jhumra Road Faisalabad',
        'housing society in Faisalabad',
        'new housing society Faisalabad',
        'gated community Faisalabad',
        'residential plots Faisalabad',
        'Faisalabad housing schemes',
        'property in Faisalabad',
        'plots near Motorway Faisalabad',
        'installment plots Khurrianwala',
    ];

    /**
     * Page slug => [SEO title, meta description, keywords]. Titles that contain the website
     * name are printed as they are (50–60 characters); descriptions are 140–160 characters.
     *
     * @var array<string, array{0: string, 1: string, 2: array<int, string>}>
     */
    public const PAGES = [
        'about' => [
            'About Model Town Khurrianwala – Gated Society in Faisalabad',
            'Model Town Khurrianwala is a gated housing society on Main Bazar Khurrianwala, Jhumra Road, Faisalabad, with 50 ft roads, parks, a mosque, gas and 24/7 security.',
            ['Model Town Khurrianwala', 'gated community Faisalabad', 'housing society in Khurrianwala', 'Jhumra Road housing society', 'society amenities Faisalabad'],
        ],
        'plots' => [
            'Plots for Sale in Faisalabad | Model Town Khurrianwala',
            '3, 5 and 10 Marla residential plots for sale in Model Town Khurrianwala, Faisalabad. See prices, sizes and easy installment plans, with no booking fee.',
            ['plots for sale in Faisalabad', '3 marla plot for sale in Faisalabad', '5 marla plot for sale in Faisalabad', '10 marla plot for sale in Faisalabad', 'residential plots Khurrianwala'],
        ],
        'installment-plans' => [
            'Plots on Installments Faisalabad | Model Town Khurrianwala',
            'Easy installment plans for 3, 5 and 10 Marla plots in Faisalabad: down payment, monthly and 6-month installments, final payment on registry. No booking fee.',
            ['plots on installments in Faisalabad', 'plot installment plan Faisalabad', '5 marla plot on installments', '3 marla plot on installments', 'easy installment plots'],
        ],
        'locations' => [
            'Location & Map – Jhumra Road | Model Town Khurrianwala',
            'Model Town Khurrianwala is on Main Bazar Khurrianwala, Jhumra Road, Faisalabad — between the Expressway and GT Road, the closest society to the Motorway.',
            ['Model Town Khurrianwala location', 'Jhumra Road Faisalabad', 'Main Bazar Khurrianwala', 'plots near Motorway Faisalabad', 'Khurrianwala map'],
        ],
        'development' => [
            'Development Progress | Model Town Khurrianwala',
            'Follow on-site development at Model Town Khurrianwala, Faisalabad: roads, utilities, parks and the commercial area, with progress updates and site photos.',
            ['Model Town Khurrianwala development', 'development progress', 'society development work Faisalabad', 'possession plots Faisalabad'],
        ],
        'gallery' => [
            'Society Photos & Videos | Model Town Khurrianwala',
            'Photos and videos of Model Town Khurrianwala, Faisalabad: the site, roads, parks, development work and site visits. See the society before you visit.',
            ['Model Town Khurrianwala photos', 'Model Town Khurrianwala videos', 'society pictures Faisalabad', 'Khurrianwala society gallery'],
        ],
        'testimonials' => [
            'Customer Reviews | Model Town Khurrianwala',
            'Read reviews from families who booked plots at Model Town Khurrianwala, Faisalabad — about the booking process, installment plans and development.',
            ['Model Town Khurrianwala reviews', 'customer reviews', 'plot buyers Faisalabad', 'testimonials'],
        ],
        'faq' => [
            'Plot Booking & Installment FAQs | Model Town Khurrianwala',
            'Answers about booking a plot at Model Town Khurrianwala, Faisalabad: location, amenities, installment plans, documents, possession and site visits.',
            ['plot booking questions', 'installment plan FAQ', 'plot documents', 'plot possession', 'Model Town Khurrianwala FAQ'],
        ],
        'contact' => [
            'Contact Us – 0300-8653838 | Model Town Khurrianwala',
            'Call or WhatsApp 0300-8653838, or call 0308-8653838, for plot prices, availability and site visits at Model Town Khurrianwala, Jhumra Road, Faisalabad.',
            ['Model Town Khurrianwala contact number', 'Model Town Khurrianwala office', 'plot prices Faisalabad', 'site visit Khurrianwala'],
        ],
        'book-plot' => [
            'Book Your Plot Online | Model Town Khurrianwala',
            'Send a booking request for a 3, 5 or 10 Marla plot at Model Town Khurrianwala, Faisalabad. Choose a size and installment plan and our team will call you.',
            ['book plot online Faisalabad', 'plot booking Khurrianwala', 'book 5 marla plot', 'plot booking form'],
        ],
    ];

    public const LOCATION = [
        'Model Town Khurrianwala Location – Jhumra Road, Faisalabad',
        'Model Town Khurrianwala is on Main Bazar Khurrianwala, Jhumra Road, Faisalabad — between the Expressway and GT Road, and the closest society to the Motorway.',
        ['Model Town Khurrianwala location', 'Main Bazar Khurrianwala', 'Jhumra Road Faisalabad', 'plots near Motorway Faisalabad', 'Khurrianwala map'],
    ];

    /**
     * SEO text written by earlier versions of the seeders. Fields that still hold one of
     * these were never edited in the admin panel, so they may be replaced.
     *
     * @var array<int, string>
     */
    public const SEEDED = [
        'Model Town Khurrianwala Faisalabad — Plots on Jhumra Road',
        'Gated community on Main Bazar Khurrianwala, Jhumra Road, Faisalabad — near the Motorway. 50 ft & 30 ft roads, parks, gas, 24/7 security. Call 0300-8653838.',
        'Model Town Khurrianwala, Khurrianwala plots, Jhumra Road plots, Faisalabad housing society, plots for sale in Faisalabad, gated community Faisalabad, plots near Motorway Faisalabad, installment plots Khurrianwala',
        'About Model Town Khurrianwala — Gated Community in Faisalabad',
        'Gated community on Main Bazar Khurrianwala, Jhumra Road, with wide roads, parks, mosque, utilities and 24/7 security.',
        'Location — Model Town Khurrianwala, Jhumra Road, Faisalabad',
        'Find Model Town Khurrianwala on Main Bazar Khurrianwala, Jhumra Road — between the Expressway and GT Road, near the Motorway.',
        'Contact Model Town Khurrianwala — 0300-8653838',
        'Call 0300-8653838 or 0308-8653838 for plot prices and availability at Model Town Khurrianwala, Jhumra Road, Faisalabad.',
        'Location — Main Bazar Khurrianwala, Jhumra Road, Faisalabad',
        'Model Town Khurrianwala is on Main Bazar Khurrianwala, Jhumra Road — between the Expressway and GT Road, and the closest society to the Motorway.',
        // First page headings and subheadings (SiteContentSeeder), copied into the SEO fields.
        'Residential plots in 3 Marla, 5 Marla, 10 Marla and 1 Kanal — each with a published installment plan.',
        'See exactly what you pay at booking, every month, at each milestone and at possession.',
        'Well Connected, Thoughtfully Located',
        'Close to main roads, the city and everyday essentials.',
        'Transparent pricing, flexible installments and visible on-ground development.',
        'Visit our office, call, WhatsApp or send a message — we are happy to help.',
        'Talk to Our Sales Team',
    ];

    protected bool $force = false;

    public function run(SettingsService $settings, bool $force = false): void
    {
        $this->force = $force;

        $this->settings($settings);
        $this->pages();
        $this->plots();
        $this->location();
    }

    protected function settings(SettingsService $settings): void
    {
        $defaults = array_map(fn (array $field) => $field['default'] ?? null, $settings->fields('seo'));
        $keywords = $settings->get('seo_keywords');

        $settings->update('seo', [
            'seo_title' => $this->replaceable($settings->get('seo_title'), [$defaults['seo_title']]) ? self::TITLE : $settings->get('seo_title'),
            'seo_description' => $this->replaceable($settings->get('seo_description'), [$defaults['seo_description']]) ? self::DESCRIPTION : $settings->get('seo_description'),
            // Keywords added in the admin panel stay; the new ones are added after them.
            'seo_keywords' => $this->replaceable($keywords, [$defaults['seo_keywords']])
                ? implode(', ', self::KEYWORDS)
                : Keywords::merge($keywords, self::KEYWORDS),
        ]);
    }

    protected function pages(): void
    {
        foreach (self::PAGES as $slug => [$title, $description, $keywords]) {
            $page = Page::query()->with('seo')->where('slug', $slug)->first();

            if ($page) {
                $this->apply($page, $title, $description, $keywords, [$page->heading, $page->subheading, $page->name]);
            }
        }
    }

    /**
     * Plot pages: "5 Marla Plot for Sale in Faisalabad", with keywords for that size.
     */
    protected function plots(): void
    {
        Plot::query()->with(['seo', 'location'])->get()->each(function (Plot $plot) {
            $seo = $plot->seo;
            $city = $plot->location?->city ?? 'Faisalabad';
            $oldTitle = (bool) preg_match('/ Plot in Model Town Khurrianwala — .+ on Installments$/u', (string) $seo?->title);

            $plot->syncSeo([
                'title' => $oldTitle || $this->replaceable($seo?->title, [$plot->title]) ? "{$plot->size_label} Plot for Sale in {$city}" : $seo?->title,
                'description' => $seo?->description ?: $plot->short_description,
                'keywords' => $this->force || blank($seo?->keywords) ? implode(', ', $plot->seoKeywords()) : $seo->keywords,
            ]);
        });
    }

    protected function location(): void
    {
        $location = Location::query()->with('seo')->where('slug', 'khurrianwala-main-site')->first();

        if ($location) {
            [$title, $description, $keywords] = self::LOCATION;
            $this->apply($location, $title, $description, $keywords, [$location->name, $location->short_description]);
        }
    }

    /**
     * @param  array<int, string>  $keywords
     * @param  array<int, string|null>  $seeded  This record's own texts that were copied into its SEO fields
     */
    protected function apply(Model $model, string $title, string $description, array $keywords, array $seeded): void
    {
        $seo = $model->seo;

        $model->syncSeo([
            'title' => $this->replaceable($seo?->title, $seeded) ? $title : $seo->title,
            'description' => $this->replaceable($seo?->description, $seeded) ? $description : $seo->description,
            'keywords' => $this->force || blank($seo?->keywords) ? implode(', ', $keywords) : $seo->keywords,
        ]);
    }

    /**
     * @param  array<int, string|null>  $seeded
     */
    protected function replaceable(?string $current, array $seeded = []): bool
    {
        return $this->force
            || blank($current)
            || in_array(trim($current), array_filter([...self::SEEDED, ...$seeded]), true);
    }
}
