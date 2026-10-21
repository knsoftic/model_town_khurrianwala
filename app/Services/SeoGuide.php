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
            $this->check('Website search engines ke liye khuli hai', $this->seo->indexable(),
                'Har page par likha hai: '.$this->seo->defaultRobots(),
                'Search engines ko website NA dikhane ka kaha gaya hai. “Show this website in search engines” ON karein.', $seoSettings, 'SEO settings'),
            $this->check('Website ka title', $title >= 30 && $title <= 60,
                "{$title} characters — bilkul theek.",
                "{$title} characters. 50–60 characters likhein, main keyword shuru mein.", $seoSettings, 'SEO settings'),
            $this->check('Website ki description', $description >= 110 && $description <= 165,
                "{$description} characters — bilkul theek.",
                "{$description} characters. 140–160 characters likhein jinhein parh kar log click karna chahein.", $seoSettings, 'SEO settings'),
            $this->check('Website keywords', $keywords >= 5,
                "{$keywords} keywords add hain.",
                "Sirf {$keywords} keywords hain. Kam az kam 5 aise phrases add karein jo buyers search karte hain.", $seoSettings, 'SEO settings'),
            $this->check('Har page ka apna title aur description', $needWork === 0,
                'Saare pages neeche wala check pass karte hain.',
                "{$needWork} pages par kaam baaqi hai — neeche “Page-by-page check” dekhein.", '#pages', 'Pages dekhein'),
            $this->check('Share image (WhatsApp / Facebook preview)', filled($this->settings->get('og_image')),
                'Upload ho chuki hai.',
                'Society ki 1200×630 photo upload karein. Link share karne par yehi photo nazar aati hai.', $seoSettings, 'SEO settings'),
            $this->check('Logo', filled($this->settings->get('logo')) || filled($this->settings->get('header_logo')),
                'Upload ho chuka hai.',
                'Logo upload karein. Google isay search results aur business panel mein dikhata hai.', route('admin.settings.edit', 'appearance'), 'Appearance'),
            $this->check('Google Search Console', filled($this->settings->get('google_site_verification')),
                'Verification code add ho chuka hai.',
                'Abhi connect nahi hai. “Technical SEO” ka step 2 follow karein.', $verification, 'Verification'),
            $this->check('Bing Webmaster Tools', filled($this->settings->get('bing_site_verification')),
                'Verification code add ho chuka hai.',
                'Abhi connect nahi hai. “Technical SEO” ka step 5 follow karein.', $verification, 'Verification'),
            $this->check('Google Analytics ya Tag Manager', filled($this->settings->get('google_analytics_id')) || filled($this->settings->get('google_tag_manager_id')),
                'Connect hai.',
                'Connect nahi hai — abhi aap visitors nahi dekh sakte. “Technical SEO” ka step 6 follow karein.', $verification, 'Verification'),
            $this->check('Social media profiles', $this->seo->socialProfiles() !== [],
                count($this->seo->socialProfiles()).' profiles website se jurri hui hain.',
                'Apne Facebook, Instagram, YouTube aur TikTok pages add karein taake Google unhein website se jor sake.', route('admin.social-links.index'), 'Social Links'),
            $this->check('Email address', filled($this->settings->get('email')),
                'Website par nazar aa raha hai.',
                'Email address nahi hai. Aik email add karein — is se Google aur buyers dono ka bharosa barhta hai.', route('admin.settings.edit', 'contact'), 'Contact settings'),
            $this->check('Demo notice bar band hai', ! $this->settings->bool('demo_notice_enabled'),
                'Band hai.',
                'Yeh bar batata hai ke kuch content sample hai. Sample testimonials aur photos badal kar isay band karein.', route('admin.settings.edit', 'general'), 'General settings'),
            $this->check('Secure address (https)', str_starts_with((string) config('app.url'), 'https://'),
                'Website ka address https:// se shuru hota hai.',
                'Server ki .env file mein APP_URL https:// se shuru hona chahiye (“Technical SEO” ka step 1 dekhein).', '#technical', 'Step dekhein'),
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
            $title < 30 ? "Title chhota hai ({$title} characters)" : null,
            $title > 65 ? "Title lamba hai ({$title} characters)" : null,
            $description === 0 ? 'Description nahi hai' : null,
            $sharedDescription ? 'Website wali description lagi hai — is page ki apni likhein' : null,
            ! $sharedDescription && $description > 0 && $description < 110 ? "Description chhoti hai ({$description} characters)" : null,
            $description > 165 ? "Description lambi hai ({$description} characters)" : null,
            str_contains((string) $seo->robots, 'noindex') ? 'Search engines se chhupa hua hai (noindex)' : null,
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
