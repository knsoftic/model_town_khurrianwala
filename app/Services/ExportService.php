<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Downloads a table as CSV, Excel (.xlsx), Word (.docx) or PDF.
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
            'docx' => $this->docx($filename, $headings, $rows, $title ?? $filename),
            default => $this->csv($filename, $headings, $rows),
        };
    }

    /**
     * Word document: a title, then each row as its own small table (label | value), which
     * stays readable on an A4 page however many columns there are. Empty values are left out.
     * The first column (e.g. ID) and second (e.g. name) form each record's heading.
     *
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    protected function docx(string $filename, array $headings, iterable $rows, string $title): Response
    {
        $rows = is_array($rows) ? $rows : iterator_to_array($rows, false);

        $body = $this->wordParagraph($this->wordRun($title, true, 36, '0B1F3A'), 0, 60)
            .$this->wordParagraph($this->wordRun('Exported '.now()->format('d M Y, h:i A').' · '.count($rows).' '.str('record')->plural(count($rows)), false, 18, '64748B'), 0, 200);

        foreach (array_values($rows) as $index => $row) {
            $row = array_values($row);
            $heading = ($index + 1).'. '.$this->plain($row[1] ?? $row[0] ?? '').(isset($row[1]) ? '  ('.$headings[0].' '.$this->plain($row[0]).')' : '');
            $body .= $this->wordParagraph($this->wordRun($heading, true, 24, '0B1F3A'), 280, 80);

            $cells = '';
            foreach ($headings as $column => $label) {
                $value = $this->plain($row[$column] ?? null);
                if ($column < 2 || $value === '') {
                    continue;
                }

                $cells .= '<w:tr>'
                    .'<w:tc><w:tcPr><w:tcW w:w="2800" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F1F5F9"/></w:tcPr>'.$this->wordParagraph($this->wordRun($label, true, 18, '334155')).'</w:tc>'
                    .'<w:tc><w:tcPr><w:tcW w:w="7106" w:type="dxa"/></w:tcPr>'.$this->wordParagraph($this->wordRun($value, false, 20)).'</w:tc>'
                    .'</w:tr>';
            }

            if ($cells !== '') {
                $border = fn (string $side) => "<w:{$side} w:val=\"single\" w:sz=\"4\" w:space=\"0\" w:color=\"CBD5E1\"/>";
                $body .= '<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders>'
                    .implode('', array_map($border, ['top', 'left', 'bottom', 'right', 'insideH', 'insideV']))
                    .'</w:tblBorders><w:tblCellMar><w:top w:w="60" w:type="dxa"/><w:left w:w="100" w:type="dxa"/><w:bottom w:w="60" w:type="dxa"/><w:right w:w="100" w:type="dxa"/></w:tblCellMar></w:tblPr>'
                    .'<w:tblGrid><w:gridCol w:w="2800"/><w:gridCol w:w="7106"/></w:tblGrid>'.$cells.'</w:tbl>';
            }
        }

        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$body
            .'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1000" w:right="1000" w:bottom="1000" w:left="1000" w:header="708" w:footer="708" w:gutter="0"/></w:sectPr>'
            .'</w:body></w:document>';

        $path = tempnam(sys_get_temp_dir(), 'docx');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>');
        $zip->addFromString('word/document.xml', $document);
        $zip->close();

        return response()->download($path, $filename.'.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }

    protected function wordParagraph(string $runs, int $before = 0, int $after = 0): string
    {
        return "<w:p><w:pPr><w:spacing w:before=\"{$before}\" w:after=\"{$after}\"/></w:pPr>{$runs}</w:p>";
    }

    /**
     * A run of text (size in half-points); line breaks are kept.
     */
    protected function wordRun(string $text, bool $bold = false, int $size = 20, ?string $color = null): string
    {
        $text = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', str_replace("\r", '', $text));
        $lines = array_map(fn (string $line) => htmlspecialchars($line, ENT_XML1 | ENT_QUOTES, 'UTF-8'), explode("\n", $text));

        return '<w:r><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>'.($bold ? '<w:b/>' : '')
            .($color ? "<w:color w:val=\"{$color}\"/>" : '')."<w:sz w:val=\"{$size}\"/></w:rPr>"
            .'<w:t xml:space="preserve">'.implode('</w:t><w:br/><w:t xml:space="preserve">', $lines).'</w:t></w:r>';
    }

    /**
     * A cell value as plain text.
     */
    protected function plain(mixed $value): string
    {
        return match (true) {
            $value instanceof \DateTimeInterface => $value->format('d M Y, h:i A'),
            $value instanceof \BackedEnum => method_exists($value, 'label') ? $value->label() : (string) $value->value,
            default => trim((string) $value),
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
