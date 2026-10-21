<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\SeoGuide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The SEO guide opened from its private link: staff who are signed in go to the admin
 * version; anyone else sees the guide on its own, without the admin menu.
 */
class SeoGuideController extends Controller
{
    public function show(Request $request, string $token, SeoGuide $guide): View|RedirectResponse
    {
        abort_unless($guide->isShareToken($token), 404);

        if ($request->user()?->can('settings')) {
            return redirect()->route('admin.seo-guide');
        }

        return view('admin.seo-guide.shared', [
            'checks' => $guide->checks(),
            'pages' => $guide->pages(),
        ]);
    }
}
