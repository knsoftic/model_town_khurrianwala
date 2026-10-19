<?php

namespace App\Services;

use App\Models\Location;
use App\Models\SeoSetting;
use App\Support\SeoData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Builds the meta tags for a public page: the record's own SEO fields first,
 * then page-specific fallbacks, then the site-wide defaults from Settings → SEO.
 */
class SeoService
{
    public function __construct(protected SettingsService $settings) {}

    /**
     * @param  array{title?: string|null, description?: string|null, image?: string|null, type?: string, schemas?: array<int, array<string, mixed>>}  $fallback
     */
    public function make(?Model $model = null, array $fallback = []): SeoData
    {
        /** @var SeoSetting|null $seo */
        $seo = $model && method_exists($model, 'seo') ? $model->loadMissing('seo')->seo : null;

        $siteName = (string) $this->settings->get('site_name', config('app.name'));
        $pageTitle = $seo?->title ?: ($fallback['title'] ?? null);
        $title = $pageTitle
            ? $pageTitle.$this->settings->get('seo_title_separator', ' | ').$siteName
            : (string) $this->settings->get('seo_title', $siteName);

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
            keywords: $seo?->keywords ?: $this->settings->get('seo_keywords'),
            canonical: $seo?->canonical_url ?: url()->current(),
            ogTitle: $seo?->og_title ?: ($pageTitle ?? $title),
            ogDescription: $seo?->og_description ?: $description,
            ogImage: $image ? url($image) : null,
            ogType: $fallback['type'] ?? 'website',
            robots: $seo?->robots,
            schemas: $schemas,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function organizationSchema(): array
    {
        $logo = media_url($this->settings->get('logo')) ?? media_url($this->settings->get('header_logo'));
        $location = Location::query()->active()->orderByDesc('is_featured')->ordered()->first(['city', 'latitude', 'longitude']);

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
        ]);
    }

    /**
     * JSON for a <script type="application/ld+json"> block; "<" is escaped so the
     * content can never close the script element.
     *
     * @param  array<string, mixed>|null  $schema
     */
    protected function encode(?array $schema): string
    {
        return (string) json_encode($schema ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    }
}
