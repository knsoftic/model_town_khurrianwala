<?php

namespace App\Services;

use App\Models\Location;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Plot;
use App\Models\Setting;
use App\Support\Keywords;
use App\Support\SeoData;
use Illuminate\Support\Str;

/**
 * The admin SEO guide: a live check of the website's SEO setup, a title and description
 * check of every public page, and the private link that opens the guide without a login.
 */
class SeoGuide
{
    public const TOKEN_KEY = 'seo_guide_token';

    /**
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $pages = null;

    public function __construct(protected SettingsService $settings, protected SeoService $seo) {}

    /**
     * The private link to the guide, created the first time it is needed.
     */
    public function shareUrl(): string
    {
        return route('seo-guide.shared', $this->settings->get(self::TOKEN_KEY) ?: $this->newShareToken());
    }

    /**
     * Replace the private link; the old one stops working.
     */
    public function newShareToken(): string
    {
        $token = Str::random(40);

        Setting::query()->updateOrCreate(['key' => self::TOKEN_KEY], ['group' => 'seo', 'type' => 'text', 'value' => $token]);
        $this->settings->flush();

        return $token;
    }

    public function isShareToken(string $token): bool
    {
        $current = (string) $this->settings->get(self::TOKEN_KEY);

        return $current !== '' && hash_equals($current, $token);
    }

    /**
     * Settings that decide how well the website does in search, each with where to fix it.
     *
     * @return array<int, array{label: string, ok: bool, detail: string, url: string, action: string}>
     */
    public function checks(): array
    {
        $title = Str::length((string) $this->settings->get('seo_title'));
        $description = Str::length((string) $this->settings->get('seo_description'));
        $keywords = count(Keywords::list($this->settings->get('seo_keywords')));
        $needWork = collect($this->pages())->where('ok', false)->count();
        $seoSettings = route('admin.settings.edit', 'seo');
        $verification = route('admin.settings.edit', 'verification');

        return [
            $this->check('Website is open to search engines', $this->seo->indexable(),
                'Pages say: '.$this->seo->defaultRobots(),
                'Search engines are told NOT to show the website. Switch on “Show this website in search engines”.', $seoSettings, 'SEO settings'),
            $this->check('Website title', $title >= 30 && $title <= 60,
                "{$title} characters — good.",
                "{$title} characters. Write 50–60 characters with the main keyword first.", $seoSettings, 'SEO settings'),
            $this->check('Website description', $description >= 110 && $description <= 165,
                "{$description} characters — good.",
                "{$description} characters. Write 140–160 characters that make people want to click.", $seoSettings, 'SEO settings'),
            $this->check('Website keywords', $keywords >= 5,
                "{$keywords} keywords added.",
                "Only {$keywords} keywords. Add the phrases buyers search for (at least 5).", $seoSettings, 'SEO settings'),
            $this->check('Every page has its own title and description', $needWork === 0,
                'All pages pass the check below.',
                "{$needWork} ".Str::plural('page', $needWork).' need attention — see “Page by page check” below.', '#pages', 'See pages'),
            $this->check('Share image (WhatsApp / Facebook preview)', filled($this->settings->get('og_image')),
                'Uploaded.',
                'Upload a 1200×630 photo of the society. It shows when a link is shared.', $seoSettings, 'SEO settings'),
            $this->check('Logo', filled($this->settings->get('logo')) || filled($this->settings->get('header_logo')),
                'Uploaded.',
                'Upload the logo. Google shows it in search results and the business panel.', route('admin.settings.edit', 'appearance'), 'Appearance'),
            $this->check('Google Search Console', filled($this->settings->get('google_site_verification')),
                'Verification code added.',
                'Not connected yet. Follow “Technical SEO” step 2.', $verification, 'Verification'),
            $this->check('Bing Webmaster Tools', filled($this->settings->get('bing_site_verification')),
                'Verification code added.',
                'Not connected yet. Follow “Technical SEO” step 5.', $verification, 'Verification'),
            $this->check('Google Analytics or Tag Manager', filled($this->settings->get('google_analytics_id')) || filled($this->settings->get('google_tag_manager_id')),
                'Connected.',
                'Not connected — you cannot see visitors yet. Follow “Technical SEO” step 6.', $verification, 'Verification'),
            $this->check('Social media profiles', $this->seo->socialProfiles() !== [],
                count($this->seo->socialProfiles()).' linked to the website.',
                'Add your Facebook, Instagram, YouTube and TikTok pages, so Google links them to the website.', route('admin.social-links.index'), 'Social Links'),
            $this->check('Email address', filled($this->settings->get('email')),
                'Shown on the website.',
                'No email address. Add one — it builds trust with Google and buyers.', route('admin.settings.edit', 'contact'), 'Contact settings'),
            $this->check('Demo notice bar is off', ! $this->settings->bool('demo_notice_enabled'),
                'Off.',
                'The bar says some content is sample content. Replace sample testimonials and photos, then switch it off.', route('admin.settings.edit', 'general'), 'General settings'),
            $this->check('Secure address (https)', str_starts_with((string) config('app.url'), 'https://'),
                'The website address starts with https://.',
                'APP_URL in the server .env file must start with https:// (see “Technical SEO” step 1).', '#technical', 'See step'),
        ];
    }

    /**
     * Title, description and keywords of every public page, plot and location, as Google sees them.
     *
     * @return array<int, array{name: string, url: string, edit: string, title: string, description: string|null, keywords: int, own_keywords: bool, ok: bool, issues: array<int, string>}>
     */
    public function pages(): array
    {
        if ($this->pages !== null) {
            return $this->pages;
        }

        $rows = [];
        $pages = Page::query()->with('seo')->get()->keyBy('slug');

        foreach (NavigationItem::ROUTES as $route => [$label, $slug]) {
            if ($slug === null) {
                $page = $pages->get('home');
                $rows[] = $this->row('Home', route($route), route('admin.settings.edit', 'seo'), $this->seo->make($page), $page?->seo?->keywords, true);

                continue;
            }

            $page = $pages->get($slug);
            if ($page && ! $page->is_active) {
                continue;
            }

            $rows[] = $this->row($label, route($route), $page ? route('admin.pages.edit', $page) : route('admin.pages.index'),
                $this->seo->make($page, ['title' => $page?->heading ?? $label]), $page?->seo?->keywords);
        }

        foreach (Plot::query()->active()->ordered()->with(['seo', 'location'])->get() as $plot) {
            $rows[] = $this->row($plot->title, route('plots.show', $plot), route('admin.plots.edit', $plot),
                $this->seo->make($plot, $plot->seoDefaults()), $plot->seo?->keywords ?: $plot->seoDefaults()['keywords']);
        }

        foreach (Location::query()->active()->ordered()->with('seo')->get() as $location) {
            $rows[] = $this->row($location->name, route('locations.show', $location), route('admin.locations.edit', $location),
                $this->seo->make($location, ['title' => $location->name, 'description' => $location->short_description]), $location->seo?->keywords);
        }

        return $this->pages = $rows;
    }

    /**
     * @return array{name: string, url: string, edit: string, title: string, description: string|null, keywords: int, own_keywords: bool, ok: bool, issues: array<int, string>}
     */
    protected function row(string $name, string $url, string $edit, SeoData $seo, ?string $ownKeywords, bool $isHome = false): array
    {
        $title = Str::length($seo->title);
        $description = Str::length((string) $seo->description);
        $sharedDescription = ! $isHome && $seo->description === Str::limit(trim(strip_tags((string) $this->settings->get('seo_description'))), 300, '');

        $issues = array_values(array_filter([
            $title < 30 ? "Title is short ({$title} characters)" : null,
            $title > 65 ? "Title is long ({$title} characters)" : null,
            $description === 0 ? 'No description' : null,
            $sharedDescription ? 'Uses the website description — write one for this page' : null,
            ! $sharedDescription && $description > 0 && $description < 110 ? "Description is short ({$description} characters)" : null,
            $description > 165 ? "Description is long ({$description} characters)" : null,
            str_contains((string) $seo->robots, 'noindex') ? 'Hidden from search engines (noindex)' : null,
        ]));

        return [
            'name' => $name,
            'url' => $url,
            'edit' => $edit,
            'title' => $seo->title,
            'description' => $seo->description,
            'keywords' => count(Keywords::list($seo->keywords)),
            'own_keywords' => filled($ownKeywords),
            'ok' => $issues === [],
            'issues' => $issues,
        ];
    }

    /**
     * @return array{label: string, ok: bool, detail: string, url: string, action: string}
     */
    protected function check(string $label, bool $ok, string $done, string $todo, string $url, string $action): array
    {
        return ['label' => $label, 'ok' => $ok, 'detail' => $ok ? $done : $todo, 'url' => $url, 'action' => $action];
    }
}
