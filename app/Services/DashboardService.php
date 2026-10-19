<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\BookingRequest;
use App\Models\GalleryImage;
use App\Models\Lead;
use App\Models\Location;
use App\Models\Plot;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $units = Plot::query()->selectRaw('COALESCE(SUM(total_units), 0) as total, COALESCE(SUM(sold_units), 0) as sold')->first();

        return [
            'plot_sizes' => Plot::query()->count(),
            'available_plots' => max(0, (int) $units->total - (int) $units->sold),
            'sold_plots' => (int) $units->sold,
            'total_leads' => Lead::query()->count(),
            'new_leads' => Lead::query()->where('status', LeadStatus::New)->count(),
            'booking_requests' => BookingRequest::query()->count(),
            'pending_bookings' => BookingRequest::query()->where('status', BookingStatus::Pending)->count(),
            'happy_clients' => Lead::query()->whereIn('status', [LeadStatus::Booked, LeadStatus::Converted])->count(),
            'testimonials' => Testimonial::query()->where('is_active', true)->count(),
            'gallery_images' => GalleryImage::query()->count(),
            'locations' => Location::query()->count(),
            'follow_ups_due' => Lead::query()->whereDate('follow_up_date', '<=', today())
                ->whereNotIn('status', [LeadStatus::Converted, LeadStatus::Cancelled])->count(),
        ];
    }

    /**
     * Chart.js configurations for the dashboard.
     *
     * @return array<string, array<string, mixed>>
     */
    public function charts(): array
    {
        return [
            'leads_over_time' => $this->leadsOverTime(),
            'monthly_leads' => $this->monthly(Lead::query(), 'Leads', '#c9a54c'),
            'booking_requests' => $this->bookingsByMonth(),
            'plot_interest' => $this->plotInterest(),
            'inquiry_sources' => $this->sources(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function leadsOverTime(): array
    {
        $start = today()->subDays(29);
        $counts = Lead::query()->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')->pluck('total', 'day');

        $labels = [];
        $values = [];
        for ($date = $start->copy(); $date->lte(today()); $date->addDay()) {
            $labels[] = $date->format('d M');
            $values[] = (int) ($counts[$date->toDateString()] ?? 0);
        }

        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Leads',
                    'data' => $values,
                    'borderColor' => '#0b1f3a',
                    'backgroundColor' => 'rgba(201,165,76,0.18)',
                    'fill' => true,
                    'tension' => 0.3,
                    'cubicInterpolationMode' => 'monotone',
                    'pointRadius' => 2,
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function monthly(Builder $query, string $label, string $color): array
    {
        [$labels, $keys] = $this->lastMonths(12);
        $counts = $query->where('created_at', '>=', Carbon::parse($keys[0].'-01'))
            ->selectRaw($this->monthExpression().' as month, COUNT(*) as total')
            ->groupBy('month')->pluck('total', 'month');

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => array_map(fn ($key) => (int) ($counts[$key] ?? 0), $keys),
                    'backgroundColor' => $color,
                    'borderRadius' => 6,
                    'maxBarThickness' => 28,
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function bookingsByMonth(): array
    {
        [$labels, $keys] = $this->lastMonths(12);
        $rows = BookingRequest::query()->where('created_at', '>=', Carbon::parse($keys[0].'-01'))
            ->selectRaw($this->monthExpression().' as month, status, COUNT(*) as total')
            ->groupBy('month', 'status')->get();

        $colors = [
            BookingStatus::Pending->value => '#38bdf8',
            BookingStatus::Contacted->value => '#818cf8',
            BookingStatus::OnHold->value => '#fbbf24',
            BookingStatus::Approved->value => '#10b981',
            BookingStatus::Rejected->value => '#f43f5e',
        ];

        $datasets = [];
        foreach (BookingStatus::cases() as $status) {
            $datasets[] = [
                'label' => $status->label(),
                'data' => array_map(fn ($key) => (int) $rows->first(fn ($row) => $row->month === $key && $row->status === $status)?->total, $keys),
                'backgroundColor' => $colors[$status->value],
                'borderRadius' => 4,
                'maxBarThickness' => 28,
            ];
        }

        return [
            'type' => 'bar',
            'data' => ['labels' => $labels, 'datasets' => $datasets],
            'options' => [
                'plugins' => ['legend' => ['display' => true, 'position' => 'bottom', 'labels' => ['boxWidth' => 10, 'usePointStyle' => true]]],
                'scales' => ['x' => ['stacked' => true, 'grid' => ['display' => false]], 'y' => ['stacked' => true, 'beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function plotInterest(): array
    {
        $rows = Lead::query()->whereNotNull('plot_id')
            ->select('plot_id', DB::raw('COUNT(*) as total'))
            ->groupBy('plot_id')->orderByDesc('total')->get();
        $plots = Plot::withTrashed()->whereIn('id', $rows->pluck('plot_id'))->pluck('size_label', 'id');

        return $this->doughnut(
            $rows->map(fn ($row) => $plots[$row->plot_id] ?? 'Removed plot')->all(),
            $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function sources(): array
    {
        $rows = Lead::query()->select('source', DB::raw('COUNT(*) as total'))->groupBy('source')->orderByDesc('total')->get();

        return $this->doughnut(
            $rows->map(fn ($row) => $row->source instanceof LeadSource ? $row->source->label() : (string) $row->source)->all(),
            $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        );
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, int>  $values
     * @return array<string, mixed>
     */
    protected function doughnut(array $labels, array $values): array
    {
        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $values,
                    'backgroundColor' => ['#0b1f3a', '#c9a54c', '#38bdf8', '#10b981', '#a78bfa', '#f97316', '#f43f5e', '#64748b', '#14b8a6', '#eab308'],
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ]],
            ],
        ];
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    protected function lastMonths(int $count): array
    {
        $labels = [];
        $keys = [];
        $month = now()->startOfMonth()->subMonths($count - 1);

        for ($i = 0; $i < $count; $i++) {
            $labels[] = $month->format('M y');
            $keys[] = $month->format('Y-m');
            $month->addMonth();
        }

        return [$labels, $keys];
    }

    protected function monthExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };
    }
}
