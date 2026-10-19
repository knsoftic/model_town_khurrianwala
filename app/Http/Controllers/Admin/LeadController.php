<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LeadRequest;
use App\Models\Lead;
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

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $leads = $this->filtered($request)
            ->with(['plot', 'assignee'])
            ->latest()
            ->paginate(25)->withQueryString();

        $counts = Lead::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.leads.index', [
            'leads' => $leads,
            'counts' => $counts,
            'total' => $counts->sum(),
            'staff' => $this->staff(),
            'plots' => Plot::withTrashed()->orderBy('sort_order')->pluck('size_label', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.leads.create', [
            'lead' => new Lead(['status' => LeadStatus::New, 'source' => LeadSource::Phone, 'assigned_to' => auth()->id()]),
            'staff' => $this->staff(),
            'plots' => Plot::query()->ordered()->pluck('size_label', 'id'),
        ]);
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $lead = Lead::query()->create($request->validated());

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead added.');
    }

    public function show(Lead $lead): View
    {
        $lead->load(['plot', 'assignee', 'notes.user', 'bookingRequest']);

        return view('admin.leads.show', [
            'lead' => $lead,
            'staff' => $this->staff(),
            'plots' => Plot::query()->ordered()->pluck('size_label', 'id'),
        ]);
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        $lead->update($request->validated());

        return back()->with('success', 'Lead updated.');
    }

    /**
     * Status / owner / follow-up changes from the list, the lead page or AJAX.
     */
    public function quickUpdate(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(LeadStatus::class)],
            'assigned_to' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')],
            'follow_up_date' => ['sometimes', 'nullable', 'date'],
        ]);

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

    public function export(Request $request, string $format, ExportService $export): Response
    {
        $leads = $this->filtered($request)->with(['plot', 'assignee'])->latest()->limit(20000)->get();

        return $export->download($format, 'leads', [
            'ID', 'Name', 'Phone', 'Email', 'Plot size', 'Budget', 'Payment preference', 'Contact by', 'Preferred time',
            'Message', 'Source', 'Status', 'Assigned to', 'Follow-up', 'Received',
        ], $leads->map(fn (Lead $lead) => [
            $lead->id, $lead->name, $lead->phone, $lead->email, $lead->plot?->size_label, $lead->budget,
            $lead->paymentPreferenceLabel(), $lead->contactMethodLabel(), $lead->preferred_time, $lead->message,
            $lead->source->label(), $lead->status->label(), $lead->assignee?->name, $lead->follow_up_date?->format('Y-m-d'),
            $lead->created_at->format('Y-m-d H:i'),
        ])->all(), 'Leads');
    }

    protected function filtered(Request $request): Builder
    {
        return Lead::query()
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
            ->when($request->query('follow_up') === 'due', fn ($query) => $query->whereDate('follow_up_date', '<=', today())
                ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Cancelled]))
            ->when($request->query('follow_up') === 'upcoming', fn ($query) => $query->whereDate('follow_up_date', '>', today()))
            ->when($request->query('from'), fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($query, $to) => $query->whereDate('created_at', '<=', $to));
    }

    /**
     * @return Collection<int, string>
     */
    protected function staff()
    {
        return User::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id');
    }
}
