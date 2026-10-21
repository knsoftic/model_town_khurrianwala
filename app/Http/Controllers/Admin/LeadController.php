<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LeadRequest;
use App\Models\Lead;
use App\Models\LeadField;
use App\Models\Plot;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staff with the "leads" permission work on every lead; agents ("my_leads") only see and
 * change the leads they added or that are assigned to them.
 */
class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $leads = $this->filtered($request)
            ->with(['plot', 'assignee', 'creator', 'nextFollowUp', 'lastFollowUp'])
            ->latest()
            ->paginate(25)->withQueryString();

        $mine = Lead::query()->visibleTo($user);
        $open = (clone $mine)->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Cancelled]);

        return view('admin.leads.index', [
            'leads' => $leads,
            'counts' => (clone $mine)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'followUpCounts' => [
                'overdue' => (clone $open)->whereDate('follow_up_date', '<', today())->count(),
                'today' => (clone $open)->whereDate('follow_up_date', today())->count(),
                'upcoming' => (clone $open)->whereDate('follow_up_date', '>', today())->count(),
                'none' => (clone $open)->whereNull('follow_up_date')->count(),
            ],
            'listFields' => LeadField::query()->active()->where('is_system', false)->where('show_in_list', true)->ordered()->get(),
            'canManage' => $user->can('leads'),
            'staff' => $this->staff(),
            'plots' => Plot::withTrashed()->orderBy('sort_order')->pluck('size_label', 'id'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.leads.create', [
            'lead' => new Lead(['status' => LeadStatus::New, 'source' => LeadSource::Facebook, 'assigned_to' => $request->user()->id]),
            'fields' => LeadField::form(),
            'staff' => $this->staff(),
        ]);
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $user = $request->user();

        $lead = Lead::query()->create($request->leadAttributes() + [
            'created_by' => $user->id,
            'assigned_to' => $user->id,
        ]);

        if ($request->filled('next_follow_up_date')) {
            $lead->followUps()->create([
                'user_id' => $user->id,
                'due_date' => $request->date('next_follow_up_date'),
                'due_time' => $request->input('next_follow_up_time'),
                'note' => $request->input('next_follow_up_note'),
            ]);
        }

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead added.');
    }

    public function show(Lead $lead): View
    {
        Gate::authorize('view', $lead);

        $lead->load([
            'plot', 'assignee', 'creator', 'notes.user', 'bookingRequest',
            // Open follow-ups first (soonest at the top), then the done ones, latest first.
            'followUps' => fn ($query) => $query->with(['user', 'doneBy'])->orderByRaw('done_at is not null')
                ->orderByRaw('case when done_at is null then due_date end asc')->orderByDesc('done_at'),
        ]);

        return view('admin.leads.show', [
            'lead' => $lead,
            'fields' => LeadField::form(),
            'staff' => $this->staff(),
        ]);
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        Gate::authorize('update', $lead);

        $lead->update($request->leadAttributes($lead));

        return back()->with('success', 'Lead updated.');
    }

    /**
     * Status / owner changes from the list, the lead page or AJAX. Only staff with "leads"
     * can hand a lead to someone else.
     */
    public function quickUpdate(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $lead);

        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(LeadStatus::class)],
            'assigned_to' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        if (array_key_exists('assigned_to', $data) && ! $request->user()->can('leads')) {
            abort(403);
        }

        if (($data['status'] ?? null) === LeadStatus::Contacted->value) {
            $data['last_contacted_at'] = now();
        }

        $lead->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lead updated',
                'badge' => '<span x-ref="badge" class="a-badge '.$lead->status->badgeClasses().'">'.e($lead->status->label()).'</span>',
            ]);
        }

        return back()->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        Gate::authorize('delete', $lead);
        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', "Lead “{$lead->name}” deleted.");
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:500'],
            'ids.*' => ['integer'],
            'action' => ['required', Rule::in(['status', 'assign', 'delete'])],
            'status' => ['required_if:action,status', 'nullable', Rule::enum(LeadStatus::class)],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $leads = Lead::query()->whereKey($data['ids'])->get();

        foreach ($leads as $lead) {
            match ($data['action']) {
                'status' => $lead->update(['status' => $data['status']]),
                'assign' => $lead->update(['assigned_to' => $data['assigned_to'] ?? null]),
                'delete' => $lead->delete(),
            };
        }

        return back()->with('success', $leads->count().' '.str('lead')->plural($leads->count()).' updated.');
    }

    /**
     * The filtered list as CSV, Excel or Word, with every field of the lead form.
     */
    public function export(Request $request, string $format, ExportService $export): Response
    {
        $leads = $this->filtered($request)
            ->with(['plot', 'assignee', 'creator', 'nextFollowUp', 'lastFollowUp'])
            ->withCount(['followUps as follow_ups_done' => fn ($query) => $query->whereNotNull('done_at')])
            ->latest()->limit(20000)->get();
        $fields = LeadField::form();

        $headings = [
            'ID', ...$fields->pluck('label'), 'Source', 'Status', 'Added by', 'Assigned to',
            'Next follow-up', 'Follow-up note', 'Last follow-up done', 'Last follow-up result', 'Follow-ups done', 'Received',
        ];

        $rows = $leads->map(fn (Lead $lead) => [
            $lead->id,
            ...$fields->map(fn (LeadField $field) => $field->display($lead)),
            $lead->source->label(), $lead->status->label(), $lead->creator?->name, $lead->assignee?->name,
            $lead->nextFollowUp?->dueLabel(), $lead->nextFollowUp?->note,
            $lead->lastFollowUp?->done_at->format('d M Y, h:i A'), $lead->lastFollowUp?->outcome,
            $lead->follow_ups_done,
            $lead->created_at->format('d M Y, h:i A'),
        ])->all();

        return $export->download($format, 'leads', $headings, $rows, 'Leads');
    }

    protected function filtered(Request $request): Builder
    {
        return Lead::query()
            ->visibleTo($request->user())
            ->search($request->query('q'))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('source'), fn ($query, $source) => $query->where('source', $source))
            ->when($request->query('plot'), fn ($query, $plot) => $query->where('plot_id', $plot))
            ->when($request->query('assigned'), function ($query, $assigned) {
                match ($assigned) {
                    'me' => $query->where('assigned_to', auth()->id()),
                    'none' => $query->whereNull('assigned_to'),
                    default => $query->where('assigned_to', $assigned),
                };
            })
            ->when($request->query('added_by'), fn ($query, $user) => $query->where('created_by', $user))
            ->when($request->query('follow_up'), function ($query, $followUp) {
                $open = fn ($query) => $query->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Cancelled]);

                match ($followUp) {
                    'due' => $open($query->whereDate('follow_up_date', '<=', today())),
                    'overdue' => $open($query->whereDate('follow_up_date', '<', today())),
                    'today' => $open($query->whereDate('follow_up_date', today())),
                    'upcoming' => $open($query->whereDate('follow_up_date', '>', today())),
                    'none' => $open($query->whereNull('follow_up_date')),
                    default => null,
                };
            })
            ->when($request->query('from'), fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($query, $to) => $query->whereDate('created_at', '<=', $to));
    }

    /**
     * @return Collection<int, string>
     */
    protected function staff(): Collection
    {
        return User::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id');
    }
}
