# ATK/ABT Management System — Rancangan Sistem

## Asumsi yang dapat dikonfigurasi

Kebijakan berikut **bukan** kebijakan perusahaan yang dikarang. Nilainya disimpan di `system_settings` / `.env` dan dapat diubah tanpa mengubah kode:

| Kunci | Default | Keterangan |
|---|---|---|
| `currency_code` | IDR | Kode mata uang laporan |
| `currency_symbol` | Rp | Simbol tampilan |
| `number_format` | ATK/ABT/{YYYY}/{MM}/{SEQ} | Format nomor pengajuan |
| `over_purchase_requires_authorization` | true | Pembelian melebihi qty disetujui diblokir tanpa otorisasi |
| `unreasonable_qty_multiplier` | 2 | Peringatan jika qty > rata-rata pemakaian × pengali |
| `email_notifications_enabled` | false | Email opsional |
| `date_format` | d/m/Y | Format tanggal UI |
| `min_stock_notification` | true | Notifikasi stok minimum |

## Arsitektur

Monolit Laravel 11 (MVC):

- **Presentation:** Blade + Tailwind + Alpine.js + Chart.js
- **HTTP:** Controllers tipis, Form Request, Policy/Middleware
- **Domain:** Service class (`RequisitionWorkflowService`, `ProcurementService`, `ReceiptService`, `StockService`, `ReportService`, `AuditService`)
- **Persistence:** MySQL + Eloquent, transaksi dan `lockForUpdate` pada stok
- **Auth:** Session (Breeze) + RBAC kustom (`roles`, `permissions`, pivot)

Frontend dan backend **satu proyek**. Status approval **tidak** diubah dari request pengguna; hanya service workflow yang boleh mengubah status.

## Workflow

```
Periode dibuka (GA/Admin)
        │
        ▼
Staff: Draft ──kirim──► Menunggu Approval Manager
        ▲                      │
        │ revisi               ├─ tolak ──► Ditolak Manager (terminal)
        │                      └─ setujui
        │                              ▼
        │                    Menunggu Verifikasi GA
        │                              │
        │◄── kembalikan ke divisi ─────┤
        │                              ├─ kunci versi final
        │                              ▼
        │                    Menunggu Approval SAM
        │                              │
        │◄── Revisi setelah SAM/SM ────┤ (ke GA)
        │                              └─ setujui
        │                              ▼
        │                    Menunggu Approval SM
        │                              │
        │◄── Revisi setelah SAM/SM ────┤ (ke GA)
        │                              └─ setujui
        ▼                    Disetujui untuk Pembelian
                         (pembelian parsial diizinkan)
                         (stok + hanya saat penerimaan sah)
```

Pembelian dilarang sebelum Manager + GA + SAM + SM selesai pada **versi pengajuan yang sama**. Perubahan setelah approval menghasilkan **versi baru** dan mengulang approval yang terdampak.

## Status terpisah

- **Workflow:** `draft`, `pending_manager`, `revision_division`, `rejected_manager`, `pending_ga`, `revision_ga`, `pending_sam`, `revision_sam_sm`, `pending_sm`, `approved_for_purchase`, `cancelled`
- **Pembelian:** `not_purchased`, `partial`, `completed`
- **Penerimaan:** `not_received`, `awaiting`, `partial`, `completed`, `needs_inspection`

## Matriks permission (inti)

| Permission | Admin | Staff | Manager | GA | SAM | SM |
|---|---|---|---|---|---|---|
| users.manage | ✓ | | | | | |
| divisions.manage | ✓ | | | | | |
| items.manage | ✓ | | | ✓ | | |
| periods.manage | ✓ | | | ✓ | | |
| settings.manage | ✓ | | | | | |
| audit.view | ✓ | | | ✓ | | |
| requisitions.create | | ✓ | | | | |
| requisitions.view_own_division | | ✓ | ✓ | | | |
| requisitions.view_all | | | | ✓ | ✓ | ✓ |
| approvals.manager | | | ✓ | | | |
| approvals.ga | | | | ✓ | | |
| approvals.sam | | | | | ✓ | |
| approvals.sm | | | | | | ✓ |
| procurement.manage | | | | ✓ | | |
| receipts.manage | | | | ✓ | | |
| stock.manage | | | | ✓ | | |
| reports.view | ✓ | ✓* | ✓* | ✓ | ✓ | ✓ |

\* Staff/Manager: laporan dibatasi divisinya.

Admin **tidak** mendapat permission approval kecuali role bisnis juga dipasang.

## Aturan keamanan approval

1. Manager hanya untuk divisi yang tercatat di `division_managers` (aktif).
2. Self-approval requester dilarang.
3. SAM tidak dapat memproses sebelum GA mengirim versi final.
4. SM tidak dapat memproses sebelum SAM menyetujui versi yang sama.
5. GA tidak boleh mengubah baris histori approval.
6. Oversight pembelian: qty dibeli kumulatif ≤ qty SM approved, kecuali ada `over_purchase_authorization`.

## ERD (ringkas)

`users` N—N `roles` N—N `permissions`  
`divisions` 1—N `users` ; `divisions` N—N `users` via `division_managers`  
`items` → `item_categories`, `units` ; `item_price_histories`  
`request_periods` 1—N `requisitions` 1—N `requisition_versions` 1—N `requisition_items`  
`requisitions` 1—N `approval_histories`  
`requisitions` 1—N `procurement_orders` 1—N `procurement_order_items`  
`procurement_orders` 1—N `goods_receipts` 1—N `goods_receipt_items`  
`items` 1—N `stock_transactions`  
`audit_logs` (append-only)

Snapshot kode/nama/harga barang disimpan pada `requisition_items` dan `procurement_order_items`.

## Halaman dan route utama

| Menu | Route | Permission |
|---|---|---|
| Dashboard | `/dashboard` | auth |
| Pengajuan Saya | `/requisitions` | requisitions.create / view_own |
| Pengajuan Masuk | `/inbox` | view_all |
| Approval Manager | `/approvals/manager` | approvals.manager |
| Verifikasi GA | `/approvals/ga` | approvals.ga |
| Approval SAM | `/approvals/sam` | approvals.sam |
| Approval SM | `/approvals/sm` | approvals.sm |
| Pembelian | `/procurements` | procurement.manage |
| Penerimaan | `/receipts` | receipts.manage |
| Rekap | `/recap` | reports.view |
| Master * | `/master/*` | *.manage |
| Stok | `/stock` | stock.manage / reports.view |
| Kebutuhan | `/needs` | reports.view |
| Laporan | `/reports` | reports.view |
| Grafik | `/charts` | reports.view |
| Notifikasi | `/notifications` | auth |
| Audit | `/audit` | audit.view |
| Pengguna | `/users` | users.manage |
| Pengaturan | `/settings` | settings.manage |
