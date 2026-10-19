<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstallmentPlanResource;
use App\Http\Resources\PlotResource;
use App\Models\Faq;
use App\Models\InstallmentPlan;
use App\Models\Location;
use App\Models\Plot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only public API used by the payment calculator and booking form,
 * and available to future mobile apps / integrations.
 */
class CatalogController extends Controller
{
    public function plots(): AnonymousResourceCollection
    {
        $plots = Plot::query()->active()->ordered()->with(['location', 'activePlans.items', 'activePlans.plot'])->get();

        return PlotResource::collection($plots)->additional(['meta' => ['currency' => setting('currency_symbol', 'PKR')]]);
    }

    public function plot(Plot $plot): PlotResource
    {
        abort_unless($plot->is_active, 404);

        return PlotResource::make($plot->load(['location', 'activePlans.items', 'activePlans.plot']))
            ->additional(['meta' => ['currency' => setting('currency_symbol', 'PKR')]]);
    }

    public function plan(InstallmentPlan $plan): InstallmentPlanResource
    {
        abort_unless($plan->is_active, 404);
        $plan->load(['items', 'plot']);
        abort_unless($plan->plot?->is_active, 404);

        return InstallmentPlanResource::make($plan);
    }

    public function locations(): JsonResponse
    {
        return response()->json(['data' => Location::query()->active()->ordered()->get()->map(fn (Location $location) => [
            'name' => $location->name,
            'slug' => $location->slug,
            'city' => $location->city,
            'area' => $location->area,
            'address' => $location->fullAddress(),
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'url' => route('locations.show', $location),
        ])]);
    }

    public function faqs(): JsonResponse
    {
        return response()->json(['data' => Faq::query()->active()->ordered()->get(['question', 'answer', 'category'])]);
    }

    public function contact(): JsonResponse
    {
        return response()->json(['data' => [
            'name' => setting('site_name'),
            'phone' => setting('phone'),
            'whatsapp' => setting('whatsapp_number'),
            'email' => setting('email'),
            'address' => setting('office_address'),
            'office_hours' => setting('office_hours'),
        ]]);
    }
}
