<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonthlyReportExport implements FromArray, WithTitle
{
    public function __construct(private array $report) {}

    public function array(): array
    {
        $rows = [
            ['Laporan Bulanan ATK/ABT'],
            ['Periode', ($this->report['filters']['month'] ?? '').'/'.($this->report['filters']['year'] ?? '')],
            [],
            ['Metrik', 'Nilai'],
            ['Total pengajuan', $this->report['total_requisitions']],
            ['Disetujui', $this->report['approved']],
            ['Ditolak', $this->report['rejected']],
            ['Qty diminta', $this->report['qty_requested']],
            ['Qty disetujui', $this->report['qty_approved']],
            ['Qty dibeli', $this->report['qty_purchased']],
            ['Qty diterima', $this->report['qty_received']],
            ['Estimasi', $this->report['estimate']],
            ['Realisasi', $this->report['actual']],
            ['Selisih', $this->report['variance']],
            [],
            ['Divisi', 'Jumlah pengajuan', 'Estimasi'],
        ];
        foreach ($this->report['by_division'] as $row) {
            $rows[] = [$row['division'], $row['count'], $row['estimate']];
        }
        $rows[] = [];
        $rows[] = ['Barang teratas', 'Qty'];
        foreach ($this->report['top_items'] as $row) {
            $rows[] = [$row['name'], $row['qty']];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Laporan Bulanan';
    }
}
