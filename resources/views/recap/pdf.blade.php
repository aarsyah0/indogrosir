<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Pembelian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 10px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        p { color: #64748b; margin: 0 0 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; }
        th { background: #eff6ff; color: #475569; font-size: 9px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>Rekap Pembelian</h1>
    <p>Periode {{ sprintf('%02d/%04d', $filters['month'] ?? now()->month, $filters['year'] ?? now()->year) }}</p>
    <table>
        <thead>
            <tr><th>Pengajuan</th><th>Divisi</th><th>Barang</th><th>Qty dibeli</th><th>Diterima</th><th>Harga</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['requisition']->number }}</td>
                    <td>{{ $row['requisition']->division?->name ?? '—' }}</td>
                    <td>{{ $row['line']->item_name_snapshot }}</td>
                    <td>{{ number_format((float) $row['purchased'], 2) }}</td>
                    <td>{{ number_format((float) $row['received'], 2) }}</td>
                    <td>{{ format_money($row['actual_price']) }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Belum ada data rekap.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
