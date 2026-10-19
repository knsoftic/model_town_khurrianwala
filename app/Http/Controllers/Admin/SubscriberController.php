<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\ExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.subscribers.index', [
            'subscribers' => NewsletterSubscriber::query()
                ->when($request->query('q'), fn ($query, $term) => $query->where('email', 'like', "%{$term}%"))
                ->latest()->paginate(50)->withQueryString(),
            'active' => NewsletterSubscriber::query()->where('is_active', true)->count(),
        ]);
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', "{$subscriber->email} removed.");
    }

    public function export(ExportService $export): Response
    {
        $rows = NewsletterSubscriber::query()->latest()->get()
            ->map(fn (NewsletterSubscriber $subscriber) => [$subscriber->email, $subscriber->is_active ? 'Active' : 'Unsubscribed', $subscriber->created_at->format('Y-m-d H:i')])
            ->all();

        return $export->download('csv', 'newsletter-subscribers', ['Email', 'Status', 'Subscribed'], $rows);
    }
}
