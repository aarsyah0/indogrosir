<?php

namespace App\Enums;

enum ReceiptStatus: string
{
    case NotReceived = 'not_received';
    case Awaiting = 'awaiting';
    case Partial = 'partial';
    case Completed = 'completed';
    case NeedsInspection = 'needs_inspection';

    public function label(): string
    {
        return match ($this) {
            self::NotReceived => 'Belum Diterima',
            self::Awaiting => 'Menunggu Penerimaan',
            self::Partial => 'Penerimaan Parsial',
            self::Completed => 'Penerimaan Selesai',
            self::NeedsInspection => 'Perlu Pemeriksaan',
        };
    }
}
