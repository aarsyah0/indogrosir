<?php

namespace App\Enums;

enum ProcurementStatus: string
{
    case NotPurchased = 'not_purchased';
    case Partial = 'partial';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NotPurchased => 'Belum Dibeli',
            self::Partial => 'Pembelian Parsial',
            self::Completed => 'Pembelian Selesai',
        };
    }
}
