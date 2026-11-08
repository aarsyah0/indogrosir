<?php

namespace App\Enums;

enum RequisitionStatus: string
{
    case Draft = 'draft';
    case PendingManager = 'pending_manager';
    case RevisionDivision = 'revision_division';
    case RejectedManager = 'rejected_manager';
    case PendingGa = 'pending_ga';
    case RevisionGa = 'revision_ga';
    case PendingSam = 'pending_sam';
    case RevisionSamSm = 'revision_sam_sm';
    case PendingSm = 'pending_sm';
    case ApprovedForPurchase = 'approved_for_purchase';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingManager => 'Menunggu Approval Manager',
            self::RevisionDivision => 'Revisi oleh Divisi',
            self::RejectedManager => 'Ditolak Manager',
            self::PendingGa => 'Menunggu Verifikasi GA',
            self::RevisionGa => 'Revisi oleh GA',
            self::PendingSam => 'Menunggu Approval SAM',
            self::RevisionSamSm => 'Revisi setelah SAM/SM',
            self::PendingSm => 'Menunggu Approval SM',
            self::ApprovedForPurchase => 'Disetujui untuk Pembelian',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::PendingManager, self::PendingGa, self::PendingSam, self::PendingSm => 'amber',
            self::RevisionDivision, self::RevisionGa, self::RevisionSamSm => 'orange',
            self::RejectedManager, self::Cancelled => 'red',
            self::ApprovedForPurchase => 'green',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::RejectedManager, self::Cancelled], true);
    }
}
