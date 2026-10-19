<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = ContactMessage::query()
            ->when($request->query('q'), fn ($query, $term) => $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")->orWhere('subject', 'like', "%{$term}%")))
            ->when($request->query('filter') === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($request->query('filter') === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.messages.index', [
            'messages' => $messages,
            'unread' => ContactMessage::query()->whereNull('read_at')->count(),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('admin.messages.show', ['item' => $message->load('lead')]);
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }

    /**
     * Turn a contact message into a lead so it can be assigned and followed up.
     */
    public function convert(ContactMessage $message): RedirectResponse
    {
        if ($message->lead) {
            return redirect()->route('admin.leads.show', $message->lead);
        }

        $lead = Lead::query()->create([
            'name' => $message->name,
            'phone' => $message->phone ?: '—',
            'email' => $message->email,
            'message' => trim(($message->subject ? $message->subject."\n\n" : '').$message->message),
            'source' => LeadSource::Contact->value,
            'status' => LeadStatus::New->value,
            'assigned_to' => auth()->id(),
            'ip_address' => $message->ip_address,
        ]);
        $message->update(['lead_id' => $lead->id]);

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Message converted to a lead and assigned to you.');
    }
}
