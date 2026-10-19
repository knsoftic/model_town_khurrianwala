<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Turns the Appearance settings into CSS custom properties consumed by the
 * Tailwind theme (see resources/css/app.css), plus the Google Fonts URL.
 */
class ThemeService
{
    public const CACHE_KEY = 'cms.theme_css';

    /**
     * Setting key => CSS variable.
     *
     * @var array<string, string>
     */
    protected const COLORS = [
        'color_primary' => '--brand-primary',
        'color_secondary' => '--brand-secondary',
        'color_accent' => '--brand-accent',
        'color_background' => '--brand-bg',
        'color_text' => '--brand-text',
        'color_button' => '--brand-button',
        'color_button_hover' => '--brand-button-hover',
        'color_button_text' => '--brand-button-text',
        'color_header_bg' => '--brand-header-bg',
        'color_header_text' => '--brand-header-text',
        'color_footer_bg' => '--brand-footer-bg',
        'color_footer_text' => '--brand-footer-text',
    ];

    public function __construct(protected SettingsService $settings) {}

    public function cssVariables(): string
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $lines = [];

            foreach (self::COLORS as $key => $variable) {
                $color = $this->color($this->settings->get($key));
                if ($color) {
                    $lines[] = "{$variable}: {$color};";
                }
            }

            // A darker shade of the accent for small text on white and the page background,
            // so labels stay readable (WCAG AA 4.5:1) whatever accent colour is chosen.
            $accent = $this->color($this->settings->get('color_accent')) ?? '#c9a54c';
            $lines[] = '--brand-accent-ink: '.$this->readableOnLight($accent, $this->color($this->settings->get('color_background')) ?? '#ffffff').';';

            $radius = max(0, min(40, (int) $this->settings->get('border_radius', 14)));
            $lines[] = "--brand-radius: {$radius}px;";
            $lines[] = '--brand-font-heading: '.$this->fontStack($this->settings->get('font_heading'), 'serif').';';
            $lines[] = '--brand-font-body: '.$this->fontStack($this->settings->get('font_body'), 'sans-serif').';';

            return ':root{'.implode('', $lines).'}';
        });
    }

    public function fontsUrl(): ?string
    {
        $fonts = config('cms.fonts');
        $families = collect([$this->settings->get('font_heading'), $this->settings->get('font_body')])
            ->filter(fn ($font) => isset($fonts[$font]))
            ->unique()
            ->map(fn ($font) => 'family='.$fonts[$font]);

        return $families->isEmpty() ? null : 'https://fonts.googleapis.com/css2?'.$families->implode('&').'&display=swap';
    }

    /**
     * Custom CSS with anything that could close the surrounding <style> element removed.
     */
    public function customCss(): string
    {
        return str_ireplace(['</style', '<script', '<!--', '-->'], '', (string) $this->settings->get('custom_css', ''));
    }

    protected function color(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value) ? $value : null;
    }

    /**
     * Darken $hex step by step until it reaches a 4.6:1 contrast ratio on white and on $background.
     */
    public function readableOnLight(string $hex, string $background = '#ffffff'): string
    {
        $rgb = $this->rgb($hex);
        $surfaces = array_filter([1.0, $this->luminance($this->rgb($background))], fn (float $luminance) => $luminance > 0.4);

        for ($factor = 1.0; $factor > 0; $factor -= 0.02) {
            $shade = array_map(fn (int $channel) => (int) round($channel * $factor), $rgb);
            $luminance = $this->luminance($shade);

            if (min(array_map(fn (float $surface) => ($surface + 0.05) / ($luminance + 0.05), $surfaces)) >= 4.6) {
                return vsprintf('#%02x%02x%02x', $shade);
            }
        }

        return '#000000';
    }

    /**
     * @return array<int, int>
     */
    protected function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return array_map('hexdec', str_split($hex, 2));
    }

    /**
     * WCAG relative luminance.
     *
     * @param  array<int, int>  $rgb
     */
    protected function luminance(array $rgb): float
    {
        [$r, $g, $b] = array_map(function (int $channel) {
            $value = $channel / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    protected function fontStack(mixed $font, string $generic): string
    {
        $fallback = $generic === 'serif'
            ? 'Georgia, "Times New Roman", serif'
            : 'ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';

        return isset(config('cms.fonts')[$font]) ? '"'.$font.'", '.$fallback : $fallback;
    }
}
