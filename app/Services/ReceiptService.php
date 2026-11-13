<?php

namespace App\Services;

use App\Enums\ReceiptStatus;
use App\Enums\StockMovementType;
use App\Exceptions\BusinessRuleException;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\ProcurementOrder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ReceiptService
{
    public function __construct(
        private AuditService $audit,
        private DocumentNumberService $numbers,
        private StockService $stock,
        private NotificationService $notifications,
        private ProcurementService $procurement,
    ) {}

    public function receive(User $user, ProcurementOrder $order, array $payload, ?UploadedFile $proof = null): GoodsReceipt
    {
        if (! $user->hasPermission('receipts.manage')) {
            throw new BusinessRuleException('Tidak berwenang mencatat penerimaan.');
        }
        $itemIds = array_column($payload['items'] ?? [], 'item_id');
        if (count($itemIds) !== count(array_unique($itemIds))) {
            throw new BusinessRuleException('Barang duplikat tidak diizinkan dalam satu penerimaan.');
        }

        return DB::transaction(function () use ($user, $order, $payload, $proof) {
            $order = ProcurementOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingReceipt = GoodsReceipt::query()
                ->where('submission_key', $payload['submission_key'])
                ->first();
            if ($existingReceipt) {
                if ($existingReceipt->procurement_order_id !== $order->id || $existingReceipt->created_by !== $user->id) {
                    throw new BusinessRuleException('Token penerimaan sudah digunakan. Muat ulang formulir sebelum mencoba kembali.');
                }

                return $existingReceipt->fresh('items');
            }

            $receipt = GoodsReceipt::query()->create([
                'number' => $this->numbers->nextSimple('GR'),
                'procurement_order_id' => $order->id,
                'submission_key' => $payload['submission_key'],
                'received_at' => $payload['received_at'],
                'notes' => $payload['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($payload['items'] as $line) {
                $poItem = $order->items()->where('item_id', $line['item_id'])->firstOrFail();
                $received = (float) $line['quantity_received'];
                $already = (float) GoodsReceiptItem::query()
                    ->where('item_id', $line['item_id'])
                    ->whereHas('receipt', fn ($q) => $q->where('procurement_order_id', $order->id))
                    ->sum('quantity_received');
                $remaining = (float) $poItem->quantity_purchased - $already;
                if ($received - $remaining > 0.0001) {
                    throw new BusinessRuleException('Kuantitas diterima melebihi sisa pembelian.');
                }
                if ($received <= 0) {
                    continue;
                }

                $short = (float) ($line['quantity_short'] ?? 0);
                if ($short - max($remaining - $received, 0) > 0.0001) {
                    throw new BusinessRuleException('Kuantitas kurang yang dilaporkan melebihi sisa pembelian.');
                }
                $condition = $line['condition'] ?? 'baik';
                $gri = GoodsReceiptItem::query()->create([
                    'goods_receipt_id' => $receipt->id,
                    'item_id' => $poItem->item_id,
                    'quantity_purchased' => $poItem->quantity_purchased,
                    'quantity_received' => $received,
                    'quantity_short' => $short,
                    'condition' => $condition,
                    'discrepancy_notes' => $line['discrepancy_notes'] ?? null,
                ]);

                $this->stock->move(
                    $user,
                    $poItem->item,
                    StockMovementType::In,
                    $received,
                    $receipt,
                    'Penerimaan '.$receipt->number,
                    $order->requisition->division_id,
                );
            }

            if ($proof) {
                $path = $proof->store('attachments/receipts');
                $receipt->attachments()->create([
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => $proof->getClientOriginalName(),
                    'mime_type' => $proof->getMimeType() ?? 'application/octet-stream',
                    'size' => $proof->getSize(),
                    'uploaded_by' => $user->id,
                ]);
            }

            $this->refreshReceiptStatus($order);
            $this->audit->log($user, 'receipt.create', $receipt, null, $receipt->toArray());

            $receiptNeedsInspection = $receipt->items()
                ->where(function ($query) {
                    $query->where('quantity_short', '>', 0)
                        ->orWhere('condition', '!=', 'baik');
                })
                ->exists();

            if ($receiptNeedsInspection) {
                $this->notifications->notifyRole(
                    'ga',
                    'Penerimaan tidak sesuai',
                    "Penerimaan {$receipt->number} perlu pemeriksaan.",
                    route('receipts.show', $receipt)
                );
            }

            return $receipt->fresh('items');
        });
    }

    private function refreshReceiptStatus(ProcurementOrder $order): void
    {
        $order->load('items', 'requisition');

        $req = $order->requisition;
        $purchasedByItem = $req->procurementOrders()
            ->with('items')
            ->get()
            ->flatMap->items
            ->groupBy('item_id')
            ->map(fn ($lines) => (float) $lines->sum('quantity_purchased'));
        $receivedByItem = GoodsReceiptItem::query()
            ->whereHas('receipt.order', fn ($q) => $q->where('requisition_id', $req->id))
            ->selectRaw('item_id, SUM(quantity_received) as quantity_received')
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');
        $requisitionNeedsInspection = GoodsReceiptItem::query()
            ->where(function ($query) {
                $query->where('quantity_short', '>', 0)
                    ->orWhere('condition', '!=', 'baik');
            })
            ->whereHas('receipt.order', fn ($q) => $q->where('requisition_id', $req->id))
            ->exists();

        $reqStatus = match (true) {
            $requisitionNeedsInspection => ReceiptStatus::NeedsInspection,
            $receivedByItem->isEmpty() => ReceiptStatus::Awaiting,
            $purchasedByItem->contains(fn ($quantity, $itemId) =>
                (float) ($receivedByItem[$itemId]->quantity_received ?? 0) + 0.0001 < (float) $quantity
            ) => ReceiptStatus::Partial,
            default => ReceiptStatus::Completed,
        };

        $req->update(['receipt_status' => $reqStatus]);
        $this->procurement->refreshProcurementStatus($req);
    }
}
