<?php

namespace App\View\Composers;

use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\SocialLink;
use App\Services\SettingsService;
use App\Services\ThemeService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Shares the admin-managed menus, social links and theme with the public layout.
 */
class FrontLayoutComposer
{
    /**
     * @var array<string, mixed>|null
     */
    protected ?array $shared = null;

    public function __construct(protected ThemeService $theme, protected SettingsService $settings) {}

    public function compose(View $view): void
    {
        $view->with($this->shared ??= $this->build());
    }

    /**
     * @return array<string, mixed>
     */
    protected function build(): array
    {
        $disabledPages = Page::query()->where('is_active', false)->pluck('slug')->all();

        /** @var Collection<string, Collection<int, NavigationItem>> $menus */
        $menus = NavigationItem::query()->active()->ordered()->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->reject(fn (NavigationItem $item) => $item->pageSlug() && in_array($item->pageSlug(), $disabledPages, true))
            ->groupBy('location');

        $allowScripts = (bool) config('cms.allow_custom_scripts');

        return [
            'headerMenu' => $menus->get('header', collect()),
            'footerQuickLinks' => $menus->get('footer_quick', collect()),
            'footerUsefulLinks' => $menus->get('footer_useful', collect()),
            'socialLinks' => SocialLink::query()->active()->ordered()->get(),
            'themeCss' => $this->theme->cssVariables(),
            'fontsUrl' => $this->theme->fontsUrl(),
            'customCss' => $this->theme->customCss(),
            'headerScripts' => $allowScripts ? (string) $this->settings->get('header_scripts', '') : '',
            'footerScripts' => $allowScripts ? (string) $this->settings->get('footer_scripts', '') : '',
            'customJs' => $allowScripts ? str_ireplace('</script', '<\/script', (string) $this->settings->get('custom_js', '')) : '',
            'analyticsId' => preg_match('/^(G|UA|GT|AW)-[A-Z0-9-]+$/i', (string) $this->settings->get('google_analytics_id')) ? $this->settings->get('google_analytics_id') : null,
            'siteVerification' => preg_match('/^[A-Za-z0-9_\-]+$/', (string) $this->settings->get('google_site_verification')) ? $this->settings->get('google_site_verification') : null,
            'logoPath' => $this->settings->get('header_logo') ?: $this->settings->get('logo'),
            'footerLogoPath' => $this->settings->get('footer_logo') ?: $this->settings->get('logo'),
            'faviconUrl' => media_url($this->settings->get('favicon')),
        ];
    }
}
