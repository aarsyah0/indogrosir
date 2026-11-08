<?php

namespace App\Support;

use App\Models\User;

class Navigation
{
    public static function items(User $user): array
    {
        $all = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => null, 'icon' => 'home', 'group' => 'Ringkasan'],
            ['label' => 'Pengajuan Masuk', 'route' => 'inbox.index', 'permission' => 'requisitions.view_all', 'icon' => 'inbox', 'group' => 'Pengajuan & Persetujuan'],
            ['label' => 'Pengajuan Saya', 'route' => 'requisitions.index', 'permission' => 'requisitions.view_own_division', 'icon' => 'file', 'group' => 'Pengajuan & Persetujuan'],
            ['label' => 'Approval Manager', 'route' => 'approvals.manager.index', 'permission' => 'approvals.manager', 'icon' => 'check', 'group' => 'Pengajuan & Persetujuan'],
            ['label' => 'Verifikasi GA', 'route' => 'approvals.ga.index', 'permission' => 'approvals.ga', 'icon' => 'search', 'group' => 'Pengajuan & Persetujuan'],
            ['label' => 'Approval SAM', 'route' => 'approvals.sam.index', 'permission' => 'approvals.sam', 'icon' => 'shield', 'group' => 'Pengajuan & Persetujuan'],
            ['label' => 'Approval SM', 'route' => 'approvals.sm.index', 'permission' => 'approvals.sm', 'icon' => 'shield', 'group' => 'Pengajuan & Persetujuan'],
            ['label' => 'Pembelian', 'route' => 'procurements.index', 'permission' => 'procurement.manage', 'icon' => 'cart', 'group' => 'Pengadaan'],
            ['label' => 'Penerimaan Barang', 'route' => 'receipts.index', 'permission' => 'receipts.manage', 'icon' => 'package', 'group' => 'Pengadaan'],
            ['label' => 'Rekap Pembelian', 'route' => 'recap.index', 'permission' => 'reports.view', 'icon' => 'table', 'group' => 'Monitoring & Laporan'],
            ['label' => 'Master Barang', 'route' => 'master.items.index', 'permission' => 'items.manage', 'icon' => 'box', 'group' => 'Master Data'],
            ['label' => 'Master Divisi', 'route' => 'master.divisions.index', 'permission' => 'divisions.manage', 'icon' => 'building', 'group' => 'Master Data'],
            ['label' => 'Master Pemasok', 'route' => 'master.suppliers.index', 'permission' => 'suppliers.manage', 'icon' => 'truck', 'group' => 'Master Data'],
            ['label' => 'Periode Pengajuan', 'route' => 'master.periods.index', 'permission' => 'periods.manage', 'icon' => 'calendar', 'group' => 'Master Data'],
            ['label' => 'Monitoring Stok', 'route' => 'stock.index', 'permission' => 'reports.view', 'icon' => 'layers', 'group' => 'Monitoring & Laporan'],
            ['label' => 'Monitoring Kebutuhan', 'route' => 'needs.index', 'permission' => 'reports.view', 'icon' => 'trend', 'group' => 'Monitoring & Laporan'],
            ['label' => 'Laporan Bulanan', 'route' => 'reports.index', 'permission' => 'reports.view', 'icon' => 'report', 'group' => 'Monitoring & Laporan'],
            ['label' => 'Grafik Penggunaan & Harga', 'route' => 'charts.index', 'permission' => 'reports.view', 'icon' => 'chart', 'group' => 'Monitoring & Laporan'],
            ['label' => 'Notifikasi', 'route' => 'notifications.index', 'permission' => null, 'icon' => 'bell', 'group' => 'Administrasi & Audit'],
            ['label' => 'Audit Trail', 'route' => 'audit.index', 'permission' => 'audit.view', 'icon' => 'list', 'group' => 'Administrasi & Audit'],
            ['label' => 'Manajemen Pengguna', 'route' => 'users.index', 'permission' => 'users.manage', 'icon' => 'users', 'group' => 'Administrasi & Audit'],
            ['label' => 'Pengaturan Sistem', 'route' => 'settings.edit', 'permission' => 'settings.manage', 'icon' => 'settings', 'group' => 'Administrasi & Audit'],
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
