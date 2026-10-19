<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plot;
use App\Services\ExportService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(): View
    {
        return view('admin.reports.index', ['reports' => ReportService::REPORTS]);
    }

    public function show(Request $request, string $report): View
    {
        abort_unless(isset(ReportService::REPORTS[$report]), 404);

        return view('admin.reports.show', [
            'key' => $report,
            'filters' => ReportService::REPORTS[$report][3],
            'report' => $this->reports->make($report, $request),
            'plots' => Plot::withTrashed()->orderBy('sort_order')->pluck('size_label', 'id'),
        ]);
    }

    public function export(Request $request, string $report, string $format, ExportService $export): Response
    {
        abort_unless(isset(ReportService::REPORTS[$report]), 404);

        $data = $this->reports->make($report, $request);

        return $export->download($format, $report.'-report', $data['headings'], $data['rows'], $data['title']);
    }
}
