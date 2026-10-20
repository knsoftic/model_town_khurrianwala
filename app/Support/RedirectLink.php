<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The admin-managed short link (Settings → Redirect Link), e.g. /leads, that
 * forwards visitors to a WhatsApp channel, community or any other link.
 */
final class RedirectLink
{
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /** First path segments the web server or the app already uses. */
    protected const RESERVED = ['api', 'build', 'storage', 'vendor', 'up', 'index.php'];

    /**
     * "Leads", "/leads/" or a pasted "https://mtkfsd.com/leads" all become "leads".
     */
    public static function normalise(?string $input): string
    {
        $input = trim((string) $input);

        if (preg_match('~^https?://~i', $input)) {
            $input = (string) parse_url($input, PHP_URL_PATH);
        }

        return strtolower(trim($input, " \t\n\r/"));
    }

    /**
     * Whether the path already belongs to a page of the website or the admin panel.
     */
    public static function isTaken(string $path): bool
    {
        if (in_array(strtok($path, '/'), [...self::RESERVED, config('cms.admin_path')], true)) {
            return true;
        }

        try {
            $route = app(Router::class)->getRoutes()->match(Request::create('/'.$path));
        } catch (HttpException) {
            return false;
        }

        return ! $route->isFallback;
    }
}
