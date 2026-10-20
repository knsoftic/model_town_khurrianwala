<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Catches every address no other page handles. When it is the admin-managed short
 * link (Settings → Redirect Link, e.g. /leads) the visitor is sent straight to the
 * saved destination; anything else is the normal "page not found".
 */
class RedirectLinkController extends Controller
{
    public function __invoke(Request $request, SettingsService $settings): RedirectResponse
    {
        $path = strtolower(trim((string) $settings->get('redirect_path'), '/'));
        $target = trim((string) $settings->get('redirect_url'));

        abort_unless(
            $settings->bool('redirect_enabled', true)
                && $path !== ''
                && strtolower(trim($request->path(), '/')) === $path
                && preg_match('~^https?://~i', $target),
            404,
        );

        // 302 + no-store: browsers never cache it, so a new destination works at once.
        return redirect()->away($target, 302)->withHeaders([
            'Cache-Control' => 'no-store, max-age=0',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
