<?php

namespace App\Support;

use App\Models\User;

class Navigation
{
    public static function items(User $user): array
    {
        $all = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => null, 'icon' => 'home'],
            ['label' => 'Pengajuan Masuk', 'route' => 'inbox.index', 'permission' => 'requisitions.view_all', 'icon' => 'inbox'],
            ['label' => 'Pengajuan Saya', 'route' => 'requisitions.index', 'permission' => 'requisitions.view_own_division', 'icon' => 'file'],
            ['label' => 'Approval Manager', 'route' => 'approvals.manager.index', 'permission' => 'approvals.manager', 'icon' => 'check'],
            ['label' => 'Verifikasi GA', 'route' => 'approvals.ga.index', 'permission' => 'approvals.ga', 'icon' => 'search'],
            ['label' => 'Approval SAM', 'route' => 'approvals.sam.index', 'permission' => 'approvals.sam', 'icon' => 'shield'],
            ['label' => 'Approval SM', 'route' => 'approvals.sm.index', 'permission' => 'approvals.sm', 'icon' => 'shield'],
            ['label' => 'Pembelian', 'route' => 'procurements.index', 'permission' => 'procurement.manage', 'icon' => 'cart'],
            ['label' => 'Penerimaan Barang', 'route' => 'receipts.index', 'permission' => 'receipts.manage', 'icon' => 'package'],
            ['label' => 'Rekap Pembelian', 'route' => 'recap.index', 'permission' => 'reports.view', 'icon' => 'table'],
            ['label' => 'Master Barang', 'route' => 'master.items.index', 'permission' => 'items.manage', 'icon' => 'box'],
            ['label' => 'Master Divisi', 'route' => 'master.divisions.index', 'permission' => 'divisions.manage', 'icon' => 'building'],
            ['label' => 'Master Pemasok', 'route' => 'master.suppliers.index', 'permission' => 'suppliers.manage', 'icon' => 'truck'],
            ['label' => 'Periode Pengajuan', 'route' => 'master.periods.index', 'permission' => 'periods.manage', 'icon' => 'calendar'],
            ['label' => 'Monitoring Stok', 'route' => 'stock.index', 'permission' => 'reports.view', 'icon' => 'layers'],
            ['label' => 'Monitoring Kebutuhan', 'route' => 'needs.index', 'permission' => 'reports.view', 'icon' => 'trend'],
            ['label' => 'Laporan Bulanan', 'route' => 'reports.index', 'permission' => 'reports.view', 'icon' => 'report'],
            ['label' => 'Grafik Penggunaan & Harga', 'route' => 'charts.index', 'permission' => 'reports.view', 'icon' => 'chart'],
            ['label' => 'Notifikasi', 'route' => 'notifications.index', 'permission' => null, 'icon' => 'bell'],
            ['label' => 'Audit Trail', 'route' => 'audit.index', 'permission' => 'audit.view', 'icon' => 'list'],
            ['label' => 'Manajemen Pengguna', 'route' => 'users.index', 'permission' => 'users.manage', 'icon' => 'users'],
            ['label' => 'Pengaturan Sistem', 'route' => 'settings.edit', 'permission' => 'settings.manage', 'icon' => 'settings'],
        ];

        return array_values(array_filter($all, function (array $item) use ($user) {
            if ($item['permission'] === null) {
                return true;
            }
            if ($item['route'] === 'requisitions.index' && $user->hasPermission('requisitions.create')) {
                return true;
            }

            return $user->hasPermission($item['permission']);
        }));
    }
}
