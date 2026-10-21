<?php

namespace App\Services;

use App\Models\Location;
use App\Models\SeoSetting;
use App\Models\SocialLink;
use App\Support\SeoData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Builds the meta tags for a public page: the record's own SEO fields first,
 * then page-specific fallbacks, then the site-wide defaults from Settings → SEO.
 */
class SeoService
{
    /**
     * Robots directives for pages search engines may show, with full-size image and text previews.
     */
    public const INDEX = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

    public const NOINDEX = 'noindex, nofollow';

    protected Location|false|null $mainLocation = null;

    /**
     * @var array<int, string>|null
     */
    protected ?array $socialProfiles = null;

    public function __construct(protected SettingsService $settings) {}

    /**
     * @param  array{title?: string|null, description?: string|null, keywords?: string|null, image?: string|null, type?: string, schemas?: array<int, array<string, mixed>>}  $fallback
     */
    public function make(?Model $model = null, array $fallback = []): SeoData
    {
        /** @var SeoSetting|null $seo */
        $seo = $model && method_exists($model, 'seo') ? $model->loadMissing('seo')->seo : null;

        $siteName = (string) $this->settings->get('site_name', config('app.name'));
        $pageTitle = $seo?->title ?: ($fallback['title'] ?? null);

        // A title that already names the website (e.g. "Plots for Sale | Model Town Khurrianwala")
        // is printed as it is, so the name is never repeated.
        $title = match (true) {
            blank($pageTitle) => (string) $this->settings->get('seo_title', $siteName),
            Str::contains($pageTitle, $siteName, ignoreCase: true) => $pageTitle,
            default => $pageTitle.$this->settings->get('seo_title_separator', ' | ').$siteName,
        };

        $description = $seo?->description ?: ($fallback['description'] ?? null) ?: $this->settings->get('seo_description');
        $description = $description ? Str::limit(trim(strip_tags($description)), 300, '') : null;

        $image = media_url($seo?->og_image) ?? media_url($fallback['image'] ?? null) ?? media_url($this->settings->get('og_image'));

        $schemas = [$this->encode($this->organizationSchema())];
        foreach ($fallback['schemas'] ?? [] as $schema) {
            $schemas[] = $this->encode($schema);
        }
        if ($seo?->schema_json && json_validate($seo->schema_json)) {
            $schemas[] = $this->encode(json_decode($seo->schema_json, true));
        }

        return new SeoData(
            title: $title,
            description: $description,
            keywords: $seo?->keywords ?: ($fallback['keywords'] ?? null) ?: $this->settings->get('seo_keywords'),
            canonical: $seo?->canonical_url ?: url()->current(),
            ogTitle: $seo?->og_title ?: ($pageTitle ?? $title),
            ogDescription: $seo?->og_description ?: $description,
            ogImage: $image ? url($image) : null,
            ogType: $fallback['type'] ?? 'website',
            robots: $this->indexable() ? ($seo?->robots ?: self::INDEX) : self::NOINDEX,
            schemas: $schemas,
            publisher: $this->publisher(),
            geo: $this->geo(),
        );
    }

    /**
     * Whether search engines may list the website (Settings → SEO → "Show this website in search engines").
     */
    public function indexable(): bool
    {
        return $this->settings->bool('seo_indexing', true);
    }

    /**
     * Site-wide robots directives, also sent as the X-Robots-Tag header. A page marked
     * "noindex" still wins, because search engines follow the strictest rule they find.
     */
    public function defaultRobots(): string
    {
        return $this->indexable() ? self::INDEX : self::NOINDEX;
    }

    public function publisher(): string
    {
        return (string) ($this->settings->get('seo_publisher') ?: $this->settings->get('site_name', config('app.name')));
    }

    /**
     * @return array<string, mixed>
     */
    public function organizationSchema(): array
    {
        $logo = media_url($this->settings->get('logo')) ?? media_url($this->settings->get('header_logo'));
        $location = $this->mainLocation();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => $this->settings->get('site_name'),
            'description' => $this->settings->get('site_tagline'),
            'url' => url('/'),
            'logo' => $logo ? url($logo) : null,
            'image' => $logo ? url($logo) : null,
            'telephone' => $this->settings->get('phone'),
            'email' => $this->settings->get('email'),
            'address' => $this->settings->get('office_address') ? array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $this->settings->get('office_address'),
                'addressLocality' => $location?->city,
                'addressCountry' => 'PK',
            ]) : null,
            // Map pin of the main project location, for Google Maps and local search results.
            'geo' => $location?->latitude && $location->longitude ? [
                '@type' => 'GeoCoordinates',
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
            ] : null,
            'hasMap' => $this->settings->get('google_maps_url') ?: null,
            'openingHours' => $this->settings->get('office_hours'),
            'areaServed' => $location?->city,
            // Social profiles from Settings → Social Links, so search engines connect them to the website.
            'sameAs' => $this->socialProfiles() ?: null,
        ]);
    }

    /**
     * @return array<int, string>
     */
    public function socialProfiles(): array
    {
        return $this->socialProfiles ??= SocialLink::query()->active()->ordered()
            ->whereNotIn('platform', ['whatsapp', 'website'])->pluck('url')->filter()->values()->all();
    }

    /**
     * The homepage "WebSite" entry: the website's name for search results.
     *
     * @return array<string, mixed>
     */
    public function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->settings->get('site_name'),
            'url' => url('/'),
            'inLanguage' => 'en-PK',
            'publisher' => ['@type' => 'Organization', 'name' => $this->publisher()],
        ];
    }

    /**
     * Breadcrumb trail for search results: Home, then the page banner's crumbs (label => URL;
     * the last one is the current page).
     *
     * @param  array<string, string|null>  $crumbs
     * @return array<string, mixed>
     */
    public function breadcrumbSchema(array $crumbs): array
    {
        $items = [['Home', route('home')]];
        foreach ($crumbs as $label => $url) {
            $items[] = [$label, $url ?: url()->current()];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $item, int $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item[0],
                'item' => $item[1],
            ], $items, array_keys($items)),
        ];
    }

    /**
     * JSON for a <script type="application/ld+json"> block; "<" is escaped so the
     * content can never close the script element.
     *
     * @param  array<string, mixed>|null  $schema
     */
    public function encode(?array $schema): string
    {
        return (string) json_encode($schema ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    }

    /**
     * geo.* meta tags for local search, from the main project location.
     *
     * @return array{region?: string|null, placename?: string|null, position?: string|null}
     */
    protected function geo(): array
    {
        $location = $this->mainLocation();

        return array_filter([
            'region' => $this->settings->get('seo_geo_region'),
            'placename' => $location ? implode(', ', array_filter(array_unique([$location->area, $location->city]))) : null,
            'position' => $location?->latitude && $location->longitude ? $location->latitude.';'.$location->longitude : null,
        ]);
    }

    protected function mainLocation(): ?Location
    {
        if ($this->mainLocation === null) {
            $this->mainLocation = Location::query()->active()->orderByDesc('is_featured')->ordered()
                ->first(['area', 'city', 'latitude', 'longitude']) ?? false;
        }

        return $this->mainLocation ?: null;
    }
}
