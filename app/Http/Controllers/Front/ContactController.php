<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ContactRequest;
use App\Http\Requests\Front\NewsletterRequest;
use App\Models\NewsletterSubscriber;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(ContactRequest $request, LeadService $leads): JsonResponse|RedirectResponse
    {
        $leads->createContactMessage($request->safe()->except('website'), $request);

        $message = setting('contact_success_message', 'Thank you for reaching out.');

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : back()->with('form_success', $message);
    }

    public function subscribe(NewsletterRequest $request): JsonResponse|RedirectResponse
    {
        NewsletterSubscriber::query()->firstOrCreate(
            ['email' => strtolower($request->validated('email'))],
            ['is_active' => true, 'ip_address' => $request->ip()],
        );

        $message = 'Thank you for subscribing!';

        return $request->expectsJson()
            ? response()->json(['message' => $message])
            : back()->with('newsletter_success', $message);
    }
}
