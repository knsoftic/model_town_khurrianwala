<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Follow-ups on a lead page: schedule one, mark it done with what came of it (and book
 * the next one in the same step), or remove one that is no longer needed.
 */
class LeadFollowUpController extends Controller
{
    public function store(Request $request, Lead $lead): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $data = $request->validate([
            'due_date' => ['required', 'date'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $followUp = $lead->followUps()->create($data + ['user_id' => $request->user()->id]);

        return back()->with('success', 'Follow-up scheduled for '.$followUp->dueLabel().'.');
    }

    /**
     * Mark a follow-up as done. The lead counts as contacted; a new lead moves to "Contacted".
     */
    public function update(Request $request, LeadFollowUp $followUp): RedirectResponse
    {
        $lead = $followUp->lead ?? abort(404);
        Gate::authorize('update', $lead);

        $data = $request->validate([
            'outcome' => ['nullable', 'string', 'max:2000'],
            'next_date' => ['nullable', 'date'],
            'next_time' => ['nullable', 'date_format:H:i'],
            'next_note' => ['nullable', 'string', 'max:500'],
        ]);

        $followUp->update(['done_at' => now(), 'done_by' => $request->user()->id, 'outcome' => $data['outcome'] ?? null]);

        $lead->update(['last_contacted_at' => now()] + ($lead->status === LeadStatus::New ? ['status' => LeadStatus::Contacted] : []));

        if (filled($data['next_date'] ?? null)) {
            $next = $lead->followUps()->create([
                'user_id' => $request->user()->id,
                'due_date' => $data['next_date'],
                'due_time' => $data['next_time'] ?? null,
                'note' => $data['next_note'] ?? null,
            ]);

            return back()->with('success', 'Follow-up marked as done. Next follow-up: '.$next->dueLabel().'.');
        }

        return back()->with('success', 'Follow-up marked as done.');
    }

    public function destroy(LeadFollowUp $followUp): RedirectResponse
    {
        Gate::authorize('update', $followUp->lead ?? abort(404));

        $followUp->delete();

        return back()->with('success', 'Follow-up removed.');
    }
}
