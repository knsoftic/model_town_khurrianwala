<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\LeadStatus;
use App\Models\BookingRequest;
use App\Models\InstallmentPlan;
use App\Models\Lead;
use App\Models\Plot;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Builds the admin reports as plain tables (headings + rows) so they can be
 * shown on screen and exported to CSV, Excel or PDF with the same data.
 */
class ReportService
{
    /**
     * @var array<string, array{0: string, 1: string, 2: string, 3: array<int, string>}>
     */
    public const REPORTS = [
        'leads' => ['Lead Report', 'Every lead with status, source, plot size and owner.', 'users-round', ['date', 'status', 'plot', 'source']],
        'bookings' => ['Booking Report', 'Booking requests with plan, status and handler.', 'bookmark-check', ['date', 'booking_status', 'plot']],
        'plot-interest' => ['Plot Interest Report', 'Leads, bookings and conversion per plot size.', 'chart-pie', ['date']],
        'plot-sizes' => ['Plot Size Report', 'Prices, payment summary and stock for each plot size.', 'land-plot', []],
        'installment-plans' => ['Installment Plan Report', 'Every plan with totals, stages, duration and bookings.', 'calendar-range', ['plot']],
        'testimonials' => ['Testimonial Report', 'Client reviews with ratings and visibility.', 'quote', []],
        'monthly-inquiries' => ['Monthly Inquiry Report', 'Leads and bookings per month, by status.', 'calendar-days', ['date']],
    ];

    /**
     * @return array{title: string, description: string, headings: array<int, string>, rows: array<int, array<int, mixed>>, summary: array<string, string>}
     */
    public function make(string $key, Request $request): array
    {
        [$title, $description] = self::REPORTS[$key];
        [$headings, $rows, $summary] = match ($key) {
            'leads' => $this->leads($request),
            'bookings' => $this->bookings($request),
            'plot-interest' => $this->plotInterest($request),
            'plot-sizes' => $this->plotSizes(),
            'installment-plans' => $this->installmentPlans($request),
            'testimonials' => $this->testimonials(),
            'monthly-inquiries' => $this->monthly($request),
        };

        return compact('title', 'description', 'headings', 'rows', 'summary');
    }

    protected function dated(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to));
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>, 2: array<string, string>}
     */
    protected function leads(Request $request): array
    {
        $leads = $this->dated(Lead::query(), $request)->with(['plot', 'assignee'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('plot'), fn ($q, $plot) => $q->where('plot_id', $plot))
            ->when($request->query('source'), fn ($q, $source) => $q->where('source', $source))
            ->latest()->limit(20000)->get();

        return [
            ['Date', 'Name', 'Phone', 'Email', 'Plot size', 'Budget', 'Source', 'Status', 'Assigned to', 'Follow-up'],
            $leads->map(fn (Lead $lead) => [
                $lead->created_at->format('Y-m-d'), $lead->name, $lead->phone, $lead->email, $lead->plot?->size_label, $lead->budget,
                $lead->source->label(), $lead->status->label(), $lead->assignee?->name, $lead->follow_up_date?->format('Y-m-d'),
            ])->all(),
            [
                'Total leads' => number_format($leads->count()),
                'New' => number_format($leads->where('status', LeadStatus::New)->count()),
                'Booked + converted' => number_format($leads->whereIn('status', [LeadStatus::Booked, LeadStatus::Converted])->count()),
                'Conversion' => $leads->count() ? round($leads->whereIn('status', [LeadStatus::Booked, LeadStatus::Converted])->count() / $leads->count() * 100, 1).'%' : '0%',
            ],
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>, 2: array<string, string>}
     */
    protected function bookings(Request $request): array
    {
        $bookings = $this->dated(BookingRequest::query(), $request)->with(['plot', 'installmentPlan', 'assignee'])
            ->when($request->query('booking_status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('plot'), fn ($q, $plot) => $q->where('plot_id', $plot))
            ->latest()->limit(20000)->get();

        return [
            ['Reference', 'Date', 'Name', 'Phone', 'Plot size', 'Installment plan', 'City', 'Status', 'Handled by'],
            $bookings->map(fn (BookingRequest $booking) => [
                $booking->reference, $booking->created_at->format('Y-m-d'), $booking->name, $booking->phone, $booking->plot?->size_label,
                $booking->installmentPlan?->name, $booking->city, $booking->status->label(), $booking->assignee?->name,
            ])->all(),
            collect(BookingStatus::cases())->mapWithKeys(fn (BookingStatus $status) => [$status->label() => number_format($bookings->where('status', $status)->count())])
                ->prepend(number_format($bookings->count()), 'Total requests')->all(),
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>, 2: array<string, string>}
     */
    protected function plotInterest(Request $request): array
    {
        $leads = $this->dated(Lead::query(), $request)->selectRaw('plot_id, COUNT(*) as total, SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as won', [LeadStatus::Booked->value, LeadStatus::Converted->value])
            ->groupBy('plot_id')->get()->keyBy('plot_id');
        $bookings = $this->dated(BookingRequest::query(), $request)->selectRaw('plot_id, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved', [BookingStatus::Approved->value])
            ->groupBy('plot_id')->get()->keyBy('plot_id');

        $rows = Plot::withTrashed()->orderBy('sort_order')->get()->map(function (Plot $plot) use ($leads, $bookings) {
            $lead = $leads->get($plot->id);
            $booking = $bookings->get($plot->id);
            $total = (int) ($lead->total ?? 0);

            return [
                $plot->size_label, $total, (int) ($lead->won ?? 0), (int) ($booking->total ?? 0), (int) ($booking->approved ?? 0),
                $total ? round(($lead->won ?? 0) / $total * 100, 1).'%' : '0%',
            ];
        })->all();

        return [
            ['Plot size', 'Leads', 'Booked / converted', 'Booking requests', 'Approved bookings', 'Conversion'],
            $rows,
            ['Most popular' => collect($rows)->sortByDesc(1)->first()[0] ?? '—', 'Leads without plot size' => number_format((int) ($leads->get('')->total ?? 0))],
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>, 2: array<string, string>}
     */
    protected function plotSizes(): array
    {
        $plots = Plot::query()->withCount(['installmentPlans', 'leads', 'bookingRequests'])->ordered()->get();

        return [
            ['Plot size', 'Status', 'On website', 'Total units', 'Sold', 'Available', 'Starting from', 'Total price', 'Booking', 'Installment', 'Plans', 'Leads', 'Bookings'],
            $plots->map(fn (Plot $plot) => [
                $plot->size_label, $plot->status->label(), $plot->is_active ? 'Yes' : 'No', $plot->total_units, $plot->sold_units, $plot->availableUnits(),
                (float) $plot->starting_price, (float) $plot->total_price, (float) $plot->booking_amount, (float) $plot->installment_amount,
                $plot->installment_plans_count, $plot->leads_count, $plot->booking_requests_count,
            ])->all(),
            [
                'Plot sizes' => number_format($plots->count()),
                'Total units' => number_format($plots->sum('total_units')),
                'Sold' => number_format($plots->sum('sold_units')),
                'Available' => number_format($plots->sum(fn (Plot $plot) => $plot->availableUnits())),
            ],
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>, 2: array<string, string>}
     */
    protected function installmentPlans(Request $request): array
    {
        $plans = InstallmentPlan::query()->with(['items', 'plot' => fn ($q) => $q->withTrashed()])->withCount('bookingRequests')
            ->when($request->query('plot'), fn ($q, $plot) => $q->where('plot_id', $plot))
            ->orderBy('plot_id')->orderBy('sort_order')->get();

        return [
            ['Plan', 'Plot size', 'Total price', 'Lump sum', 'Stages', 'Installments', 'Installment amount', 'Duration (months)', 'Balanced', 'Default', 'On website', 'Bookings'],
            $plans->map(function (InstallmentPlan $plan) {
                $s = $plan->summary();

                return [
                    $plan->name, $plan->plot?->size_label, $s->totalPrice, (float) $plan->full_payment_price, count($s->stages), $s->installmentCount,
                    $s->installmentAmount, $s->durationMonths, $s->isBalanced() ? 'Yes' : 'No', $plan->is_default ? 'Yes' : 'No',
                    $plan->is_active ? 'Yes' : 'No', $plan->booking_requests_count,
                ];
            })->all(),
            ['Plans' => number_format($plans->count()), 'Not balanced' => number_format($plans->reject(fn ($plan) => $plan->summary()->isBalanced())->count())],
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>, 2: array<string, string>}
     */
    protected function testimonials(): array
    {
        $testimonials = Testimonial::query()->with('plot')->ordered()->get();

        return [
            ['Client', 'Location', 'Plot size', 'Rating', 'Video', 'Featured', 'On website', 'Added'],
            $testimonials->map(fn (Testimonial $t) => [
                $t->client_name, $t->location, $t->plot?->size_label, $t->rating, $t->video_url ? 'Yes' : 'No',
                $t->is_featured ? 'Yes' : 'No', $t->is_active ? 'Yes' : 'No', $t->created_at->format('Y-m-d'),
            ])->all(),
            [
                'Testimonials' => number_format($testimonials->count()),
                'Average rating' => $testimonials->count() ? number_format($testimonials->avg('rating'), 1).' / 5' : '—',
                'Published' => number_format($testimonials->where('is_active', true)->count()),
            ],
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>, 2: array<string, string>}
     */
    protected function monthly(Request $request): array
    {
        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfMonth() : now()->startOfMonth()->subMonths(11);
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfMonth() : now()->endOfMonth();

        $leads = Lead::query()->whereBetween('created_at', [$from, $to])->get(['status', 'created_at'])
            ->groupBy(fn (Lead $lead) => $lead->created_at->format('Y-m'));
        $bookings = BookingRequest::query()->whereBetween('created_at', [$from, $to])->get(['created_at'])
            ->groupBy(fn (BookingRequest $booking) => $booking->created_at->format('Y-m'));

        $rows = [];
        for ($month = $from->copy(); $month->lte($to); $month->addMonth()) {
            $key = $month->format('Y-m');
            $monthLeads = $leads->get($key, collect());
            $rows[] = array_merge(
                [$month->format('M Y'), $monthLeads->count()],
                array_map(fn (LeadStatus $status) => $monthLeads->where('status', $status)->count(), LeadStatus::cases()),
                [$bookings->get($key, collect())->count()],
            );
        }

        return [
            array_merge(['Month', 'Total leads'], array_map(fn (LeadStatus $status) => $status->label(), LeadStatus::cases()), ['Booking requests']),
            $rows,
            ['Months' => (string) count($rows), 'Leads' => number_format(array_sum(array_column($rows, 1))), 'Best month' => collect($rows)->sortByDesc(1)->first()[0] ?? '—'],
        ];
    }
}
