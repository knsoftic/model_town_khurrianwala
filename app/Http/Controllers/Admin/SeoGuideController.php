<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\SeoGuide;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeoGuideController extends Controller
{
    public function __construct(protected SeoGuide $guide) {}

    public function index(): View
    {
        return view('admin.seo-guide.index', [
            'checks' => $this->guide->checks(),
            'pages' => $this->guide->pages(),
            'shareUrl' => $this->guide->shareUrl(),
        ]);
    }

    /**
     * Replace the private link, e.g. after the person it was sent to no longer needs it.
     */
    public function resetLink(ActivityLogger $logger): RedirectResponse
    {
        $this->guide->newShareToken();
        $logger->log('updated', 'Made a new private link for the SEO guide');

        return redirect()->route('admin.seo-guide')->with('success', 'Naya link ban gaya. Purana link ab guide nahi kholta.');
    }
}
