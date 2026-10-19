<?php

namespace App\Http\Controllers\Front;

use App\Models\Plot;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallmentPlanController extends FrontController
{
    public function index(Request $request): View
    {
        $page = $this->page('installment-plans');

        $plots = Plot::query()->active()->ordered()
            ->whereHas('installmentPlans', fn ($query) => $query->where('is_active', true))
            ->with(['activePlans.items'])
            ->get();

        $selected = $plots->firstWhere('slug', $request->query('plot')) ?? $plots->first();

        return view('front.installment-plans', [
            'page' => $page,
            'plots' => $plots,
            'selected' => $selected,
            'seo' => $this->seoFor($page, ['title' => $page->heading ?? 'Installment Plans']),
        ]);
    }
}
