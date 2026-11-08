<?php

namespace App\Enums;

enum ApprovalAction: string
{
    case Approved = 'approved';
    case Returned = 'returned';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Disetujui',
            self::Returned => 'Dikembalikan',
            self::Rejected => 'Ditolak',
        };
    }
}
