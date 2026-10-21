<?php

namespace App\Http\Controllers\Front;

use App\Services\HomepageService;
use Illuminate\View\View;

class HomeController extends FrontController
{
    public function index(HomepageService $homepage): View
    {
        $page = $this->page('home');

        return view('front.home', $homepage->build() + [
            'seo' => $this->seoFor($page->exists ? $page : null, ['schemas' => [$this->seo->websiteSchema()]]),
        ]);
    }
}
