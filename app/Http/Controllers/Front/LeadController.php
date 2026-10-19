<?php

namespace App\Http\Controllers\Front;

use App\Enums\LeadSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\CallbackRequest;
use App\Http\Requests\Front\LeadRequest;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LeadController extends Controller
{
    public function __construct(protected LeadService $leads) {}

    public function store(LeadRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->except(['website', 'source']);
        $source = LeadSource::tryFrom((string) $request->validated('source')) ?? LeadSource::Inquiry;

        $this->leads->createLead($data, $source, $request);

        return $this->respond($request->expectsJson(), setting('lead_success_message'));
    }

    public function callback(CallbackRequest $request): JsonResponse|RedirectResponse
    {
        $this->leads->createLead($request->safe()->except('website'), LeadSource::Callback, $request);

        return $this->respond($request->expectsJson(), setting('callback_success_message'));
    }

    protected function respond(bool $json, ?string $message): JsonResponse|RedirectResponse
    {
        $message ??= 'Thank you! We will contact you shortly.';

        return $json
            ? response()->json(['message' => $message])
            : back()->with('form_success', $message);
    }
}
