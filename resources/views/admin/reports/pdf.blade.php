<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 9px; color: #1e293b; margin: 0; }
        .head { border-bottom: 3px solid #c9a54c; padding-bottom: 8px; margin-bottom: 12px; }
        .head h1 { font-size: 16px; margin: 0; color: #0b1f3a; }
        .head p { margin: 3px 0 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0b1f3a; color: #fff; text-align: left; padding: 5px 6px; font-size: 8.5px; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .foot { margin-top: 10px; color: #94a3b8; font-size: 8px; }
    </style>
</head>
<body>
    <div class="head">
        <h1>{{ $title }}</h1>
        <p>{{ setting('site_name') }} · Generated {{ now()->format('d M Y, h:i A') }} · {{ count($rows) }} {{ str('row')->plural(count($rows)) }}</p>
    </div>
    <table>
        <thead><tr>@foreach ($headings as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>@foreach ($row as $cell)<td>{{ is_scalar($cell) || $cell === null ? $cell : (string) $cell }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">No records for the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="foot">Confidential — for internal use only.</p>
</body>
</html>
