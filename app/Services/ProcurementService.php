<?php

namespace App\Services;

use App\Enums\ProcurementStatus;
use App\Enums\ReceiptStatus;
use App\Enums\RequisitionStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Item;
use App\Models\ItemPriceHistory;
use App\Models\ProcurementOrder;
use App\Models\ProcurementOrderItem;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcurementService
{
    public function __construct(
        private AuditService $audit,
        private DocumentNumberService $numbers,
        private NotificationService $notifications,
    ) {}

    public function create(User $user, Requisition $requisition, array $payload, ?UploadedFile $proof = null): ProcurementOrder
    {
        if (! $user->hasPermission('procurement.manage')) {
            throw new BusinessRuleException('Tidak berwenang mencatat pembelian.');
        }
        if ($requisition->status !== RequisitionStatus::ApprovedForPurchase) {
            throw new BusinessRuleException('Pengajuan belum mendapat seluruh approval wajib.');
        }

        $version = $requisition->latestVersion();
        $approvedMap = $version->items->keyBy('item_id');
        $lines = $payload['items'] ?? [];
        if ($lines === []) {
            throw new BusinessRuleException('Minimal satu barang harus dibeli.');
        }
        $itemIds = array_column($lines, 'item_id');
        if (count($itemIds) !== count(array_unique($itemIds))) {
            throw new BusinessRuleException('Barang duplikat tidak diizinkan dalam satu pembelian.');
        }

        return DB::transaction(function () use ($user, $requisition, $payload, $proof, $approvedMap, $lines) {
            $requisition = Requisition::query()
                ->whereKey($requisition->id)
                ->lockForUpdate()
                ->firstOrFail();
            if ($requisition->status !== RequisitionStatus::ApprovedForPurchase) {
                throw new BusinessRuleException('Pengajuan belum mendapat seluruh approval wajib.');
            }
            $approvedMap = $requisition->latestVersion()?->items->keyBy('item_id') ?? collect();

            $over = (bool) ($payload['over_qty_authorized'] ?? false);
            if ($over && ! $user->hasPermission('procurement.authorize_over_qty')) {
                throw new BusinessRuleException('Anda tidak memiliki otorisasi pembelian melebihi persetujuan.');
            }
            if ($over && blank($payload['over_qty_reason'] ?? null)) {
                throw new BusinessRuleException('Alasan otorisasi pembelian berlebih wajib diisi.');
            }

            $order = ProcurementOrder::query()->create([
                'number' => $this->numbers->nextSimple('PO'),
                'requisition_id' => $requisition->id,
                'supplier_id' => $payload['supplier_id'],
                'purchased_at' => $payload['purchased_at'],
                'invoice_number' => $payload['invoice_number'] ?? null,
                'discount' => $payload['discount'] ?? 0,
                'tax' => $payload['tax'] ?? 0,
                'extra_cost' => $payload['extra_cost'] ?? 0,
                'over_qty_authorized' => $over,
                'over_qty_reason' => $over ? $payload['over_qty_reason'] : null,
                'notes' => $payload['notes'] ?? null,
                'created_by' => $user->id,
                'actual_total' => 0,
            ]);

            $total = 0;
            foreach ($lines as $line) {
                $qty = (float) $line['quantity_purchased'];
                if ($qty <= 0) {
                    continue;
                }
                $reqItem = $approvedMap->get($line['item_id']);
                if (! $reqItem) {
                    throw new BusinessRuleException('Barang tidak ada pada pengajuan yang disetujui.');
                }
                $approved = (float) $reqItem->quantity_sm_approved;
                $already = (float) ProcurementOrderItem::query()
                    ->where('item_id', $line['item_id'])
                    ->whereHas('order', fn ($q) => $q->where('requisition_id', $requisition->id))
                    ->sum('quantity_purchased');

                if (($already + $qty) - $approved > 0.0001) {
                    $requiresAuth = (bool) setting('over_purchase_requires_authorization', true);
                    if ($requiresAuth && ! $over) {
                        throw new BusinessRuleException('Pembelian melebihi kuantitas yang disetujui diblokir sampai ada otorisasi tambahan.');
                    }
                    if ($requiresAuth && $over && ! $user->hasPermission('procurement.authorize_over_qty')) {
                        throw new BusinessRuleException('Otorisasi pembelian berlebih tidak valid.');
                    }
                }

                $item = Item::query()->with('unit')->findOrFail($line['item_id']);
                $price = (float) $line['unit_price'];
                $subtotal = $qty * $price;
                $total += $subtotal;

                ProcurementOrderItem::query()->create([
                    'procurement_order_id' => $order->id,
                    'item_id' => $item->id,
                    'item_code_snapshot' => $item->code,
                    'item_name_snapshot' => $item->name,
                    'unit_name_snapshot' => $item->unit->name,
                    'quantity_approved' => $approved,
                    'quantity_purchased' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                ItemPriceHistory::query()->create([
                    'item_id' => $item->id,
                    'price' => $price,
                    'source' => 'procurement',
                    'recorded_by' => $user->id,
                    'notes' => 'Snapshot harga pembelian '.$order->number,
                    'recorded_at' => now(),
                ]);
            }

            $grand = $total - (float) $order->discount + (float) $order->tax + (float) $order->extra_cost;
            $order->update(['actual_total' => $grand]);

            if ($proof) {
                $this->storeProof($user, $order, $proof);
            }

            $this->refreshProcurementStatus($requisition);
            $this->audit->log($user, 'procurement.create', $order, null, $order->fresh()->toArray());
            $this->notifications->notifyRole('ga', 'Pembelian dicatat', "Transaksi {$order->number} untuk {$requisition->number}.", route('procurements.show', $order));

            return $order->fresh('items');
        });
    }

    public function refreshProcurementStatus(Requisition $requisition): void
    {
        $version = $requisition->latestVersion();
        $approvedByItem = $version->items()->get()->keyBy('item_id');
        $boughtByItem = ProcurementOrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('requisition_id', $requisition->id))
            ->selectRaw('item_id, SUM(quantity_purchased) as quantity_purchased')
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');
        $hasPurchases = $boughtByItem->isNotEmpty();
        $hasRemaining = $approvedByItem->contains(fn ($item, $itemId) =>
            (float) ($boughtByItem[$itemId]->quantity_purchased ?? 0)
                + 0.0001 < (float) $item->quantity_sm_approved
        );

        $status = match (true) {
            ! $hasPurchases => ProcurementStatus::NotPurchased,
            $hasRemaining => ProcurementStatus::Partial,
            default => ProcurementStatus::Completed,
        };

        $requisition->update(['procurement_status' => $status]);
        if ($hasPurchases && in_array($requisition->receipt_status, [ReceiptStatus::NotReceived, ReceiptStatus::Awaiting], true)) {
            $requisition->update(['receipt_status' => ReceiptStatus::Awaiting]);
        }
    }

    private function storeProof(User $user, ProcurementOrder $order, UploadedFile $file): void
    {
        $allowed = config('atkabt.allowed_upload_mimes');
        if (! in_array(strtolower($file->getClientOriginalExtension()), $allowed, true)) {
            throw new BusinessRuleException('Tipe berkas tidak diizinkan.');
        }
        if ($file->getSize() > config('atkabt.max_upload_kb') * 1024) {
            throw new BusinessRuleException('Ukuran berkas melebihi batas.');
        }

        $path = $file->store('attachments/procurement');
        $order->attachments()->create([
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);
    }
}
