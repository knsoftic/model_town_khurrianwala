<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the admin panel out of search engines.
 */
class NoIndex
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
