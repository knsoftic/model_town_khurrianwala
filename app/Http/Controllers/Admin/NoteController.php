<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\Lead;
use App\Models\Note;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function storeForLead(Request $request, Lead $lead): RedirectResponse
    {
        return $this->store($request, $lead);
    }

    public function storeForBooking(Request $request, BookingRequest $booking): RedirectResponse
    {
        return $this->store($request, $booking);
    }

    /**
     * Notes can be removed by their author or a Super Admin.
     */
    public function destroy(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id || $request->user()->isSuperAdmin(), 403);

        $note->delete();

        return back()->with('success', 'Note deleted.');
    }

    protected function store(Request $request, Lead|BookingRequest $record): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        /** @var Model&Lead $record */
        $record->notes()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);

        return back()->with('success', 'Note added.');
    }
}
