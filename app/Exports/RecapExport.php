<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;

class RecapExport implements FromArray
{
    public function __construct(private Collection $rows) {}

    public function array(): array
    {
        $out = [[
            'Nomor', 'Divisi', 'Barang', 'Diminta', 'Manager', 'GA', 'SAM/SM',
            'Dibeli', 'Diterima', 'Kurang', 'Harga ref', 'Harga aktual', 'Estimasi', 'Realisasi', 'Status',
        ]];
        foreach ($this->rows as $row) {
            $line = $row['line'];
            $out[] = [
                $row['requisition']->number,
                $row['requisition']->division?->name,
                $line->item_name_snapshot,
                $line->quantity_requested,
                $line->quantity_manager_approved,
                $line->quantity_ga_final,
                $line->quantity_sm_approved,
                $row['purchased'],
                $row['received'],
                $row['short'],
                $line->reference_unit_price,
                $row['actual_price'],
                $row['estimate'],
                $row['actual_cost'],
                $row['status'],
            ];
        }

        return $out;
    }
}
