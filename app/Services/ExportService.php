<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads a table as CSV, Excel (.xlsx) or PDF.
 * Text cells that start with = + - @ are prefixed so spreadsheets never run them as formulas.
 */
class ExportService
{
    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function download(string $format, string $filename, array $headings, iterable $rows, ?string $title = null): Response
    {
        $filename = $filename.'-'.now()->format('Y-m-d');

        return match ($format) {
            'xlsx' => $this->xlsx($filename, $headings, $rows, $title),
            'pdf' => $this->pdf($filename, $headings, $rows, $title ?? $filename),
            default => $this->csv($filename, $headings, $rows),
        };
    }

    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    protected function csv(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headings, ',', '"', '');
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($value) => $this->safe($value), $row), ',', '"', '');
            }
            fclose($out);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    protected function xlsx(string $filename, array $headings, iterable $rows, ?string $title): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title ?? 'Export', 0, 31));
        $sheet->fromArray($headings, null, 'A1');

        $line = 2;
        foreach ($rows as $row) {
            $sheet->fromArray(array_map(fn ($value) => $this->safe($value), array_values($row)), null, 'A'.$line++, true);
        }

        $last = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$last}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle("A1:{$last}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0B1F3A');
        $sheet->freezePane('A2');
        foreach (range(1, count($headings)) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        return response()->streamDownload(
            fn () => (new Xlsx($spreadsheet))->save('php://output'),
            $filename.'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    protected function pdf(string $filename, array $headings, iterable $rows, string $title): Response
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('admin.reports.pdf', [
            'title' => $title,
            'headings' => $headings,
            'rows' => is_array($rows) ? $rows : iterator_to_array($rows, false),
        ])->render());
        $dompdf->setPaper('A4', count($headings) > 6 ? 'landscape' : 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
        ]);
    }

    protected function safe(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i');
        }

        if ($value instanceof \BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : $value->value;
        }

        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! is_numeric($value)) {
            return "'".$value;
        }

        return $value;
    }
}
