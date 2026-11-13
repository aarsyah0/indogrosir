<?php

namespace App\Support;

class PermissionCatalog
{
    public const GROUPS = [
        'users' => [
            'users.manage' => 'Mengelola pengguna, role, dan permission',
        ],
        'master' => [
            'divisions.manage' => 'Mengelola divisi dan manager divisi',
            'items.manage' => 'Mengelola barang, kategori, dan satuan',
            'suppliers.manage' => 'Mengelola pemasok',
            'periods.manage' => 'Mengelola periode pengajuan',
            'settings.manage' => 'Mengelola konfigurasi sistem',
        ],
        'requisitions' => [
            'requisitions.create' => 'Membuat dan mengirim pengajuan divisi',
            'requisitions.view_own_division' => 'Melihat pengajuan milik divisi sendiri',
            'requisitions.view_all' => 'Melihat seluruh pengajuan',
        ],
        'approvals' => [
            'approvals.manager' => 'Approval Manager divisi',
            'approvals.ga' => 'Verifikasi General Affairs',
            'approvals.sam' => 'Approval SAM',
            'approvals.sm' => 'Approval SM',
        ],
        'operations' => [
            'procurement.manage' => 'Mencatat pembelian',
            'procurement.authorize_over_qty' => 'Mengotorisasi pembelian melebihi kuantitas disetujui',
            'procurement.authorize_over_qty' => 'Otorisasi pembelian melebihi kuantitas disetujui',
            'receipts.manage' => 'Mencatat penerimaan barang',
            'stock.manage' => 'Mengelola stok dan penyesuaian',
        ],
        'reports' => [
            'reports.view' => 'Melihat laporan, rekap, dan grafik',
            'audit.view' => 'Melihat audit trail',
        ],
    ];

    public const ROLE_PERMISSIONS = [
        'admin' => [
            'users.manage',
            'divisions.manage',
            'items.manage',
            'suppliers.manage',
            'periods.manage',
            'settings.manage',
            'audit.view',
            'reports.view',
            'requisitions.view_all',
        ],
        'staff' => [
            'requisitions.create',
            'requisitions.view_own_division',
            'reports.view',
        ],
        'manager' => [
            'requisitions.view_own_division',
            'approvals.manager',
            'reports.view',
        ],
        'ga' => [
            'requisitions.view_all',
            'approvals.ga',
            'items.manage',
            'suppliers.manage',
            'periods.manage',
            'procurement.manage',
            'procurement.authorize_over_qty',
            'receipts.manage',
            'stock.manage',
            'reports.view',
            'audit.view',
        ],
        'sam' => [
            'requisitions.view_all',
            'approvals.sam',
            'reports.view',
        ],
        'sm' => [
            'requisitions.view_all',
            'approvals.sm',
            'reports.view',
        ],
    ];

    public static function all(): array
    {
        $all = [];
        foreach (self::GROUPS as $permissions) {
            $all = array_merge($all, $permissions);
        }

        return $all;
    }
}
