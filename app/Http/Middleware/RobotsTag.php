<?php

namespace App\Http\Middleware;

use App\Services\SeoService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends the site-wide robots directives as an X-Robots-Tag header on public pages.
 * Responses that already carry one (the admin panel, shared SEO guide) keep their own.
 */
class RobotsTag
{
    public function __construct(protected SeoService $seo) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response->headers->has('X-Robots-Tag')) {
            $response->headers->set('X-Robots-Tag', $this->seo->defaultRobots());
        }

        return $response;
    }
}
