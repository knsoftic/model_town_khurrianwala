<?php

use App\Services\ResponsiveImages;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

if (! function_exists('setting')) {
    /**
     * Read an admin-managed website setting.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}

if (! function_exists('setting_enabled')) {
    function setting_enabled(string $key, bool $default = false): bool
    {
        return app(SettingsService::class)->bool($key, $default);
    }
}

if (! function_exists('media_url')) {
    /**
     * Public URL for an uploaded file (or an absolute URL passed through).
     */
    function media_url(?string $path, ?string $fallback = null): ?string
    {
        if (blank($path)) {
            return $fallback;
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('img_attrs')) {
    /**
     * src / srcset / sizes / width / height for an <img>, serving resized WebP
     * copies of uploads: <img {{ img_attrs($path, '(min-width: 1024px) 50vw, 100vw') }} alt="">.
     * Pass $height for fixed-height images such as logos.
     *
     * @param  array<int, int>  $widths
     */
    function img_attrs(?string $path, string $sizes = '100vw', array $widths = [480, 768, 960, 1600], ?int $height = null): HtmlString
    {
        $attributes = app(ResponsiveImages::class)->attributes($path, $sizes, $widths, $height);

        return new HtmlString(collect($attributes)->map(fn ($value, $name) => $name.'="'.e($value).'"')->implode(' '));
    }
}

if (! function_exists('img_preload')) {
    /**
     * <link rel="preload"> for the above-the-fold image, so the browser starts
     * downloading it before it has read the stylesheet and the page body.
     *
     * @param  array<int, int>  $widths
     */
    function img_preload(?string $path, string $sizes = '100vw', array $widths = [480, 768, 960, 1600]): HtmlString
    {
        $attributes = app(ResponsiveImages::class)->attributes($path, $sizes, $widths);

        if (! isset($attributes['src'])) {
            return new HtmlString('');
        }

        $link = '<link rel="preload" as="image" href="'.e($attributes['src']).'"';

        if (isset($attributes['srcset'])) {
            $link .= ' imagesrcset="'.e($attributes['srcset']).'" imagesizes="'.e($attributes['sizes']).'"';
        }

        return new HtmlString($link.' fetchpriority="high">');
    }
}

if (! function_exists('img_url')) {
    /**
     * URL of an upload resized to $width (falls back to the original file).
     */
    function img_url(?string $path, int $width = 1600): ?string
    {
        return app(ResponsiveImages::class)->url($path, $width);
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount with the configured currency label, e.g. "PKR 3,000,000" or "PKR 30 Lakh".
     */
    function money(mixed $amount, ?bool $short = null): string
    {
        $currency = setting('currency_symbol', 'PKR');

        if ($amount === null || $amount === '') {
            return '—';
        }

        $short ??= setting('price_display') === 'short';

        return trim($currency.' '.($short ? money_short((float) $amount) : number_format((float) $amount)));
    }
}

if (! function_exists('money_short')) {
    /**
     * South-Asian short form: 2,500,000 => "25 Lakh", 12,500,000 => "1.25 Crore".
     */
    function money_short(float $amount): string
    {
        $trim = fn (float $value) => rtrim(rtrim(number_format($value, 2), '0'), '.');

        return match (true) {
            $amount >= 10000000 => $trim($amount / 10000000).' Crore',
            $amount >= 100000 => $trim($amount / 100000).' Lakh',
            $amount >= 1000 => $trim($amount / 1000).'K',
            default => number_format($amount),
        };
    }
}

if (! function_exists('whatsapp_url')) {
    /**
     * wa.me link for the configured WhatsApp number with a pre-filled message.
     */
    function whatsapp_url(?string $message = null): ?string
    {
        $number = preg_replace('/\D/', '', (string) setting('whatsapp_number'));

        if ($number === '') {
            return null;
        }

        $message ??= setting('whatsapp_default_message');

        return 'https://wa.me/'.$number.($message ? '?text='.rawurlencode($message) : '');
    }
}

if (! function_exists('tel_url')) {
    function tel_url(?string $phone): ?string
    {
        $clean = preg_replace('/[^\d+]/', '', (string) $phone);

        return $clean === '' ? null : 'tel:'.$clean;
    }
}

if (! function_exists('admin_path')) {
    function admin_path(): string
    {
        return config('cms.admin_path', 'admin');
    }
}
