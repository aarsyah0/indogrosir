<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\BusinessRuleException;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function __construct(private AuditService $audit) {}

    public function currentBalance(int $itemId): float
    {
        $last = StockTransaction::query()
            ->where('item_id', $itemId)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        return $last ? (float) $last->balance_after : 0.0;
    }

    public function move(
        User $actor,
        Item $item,
        StockMovementType $type,
        float $quantity,
        ?Model $reference = null,
        ?string $notes = null,
        ?int $divisionId = null,
    ): StockTransaction {
        if ($quantity <= 0) {
            throw new BusinessRuleException('Kuantitas stok harus lebih dari nol.');
        }

        return DB::transaction(function () use ($actor, $item, $type, $quantity, $reference, $notes, $divisionId) {
            $item = Item::query()->whereKey($item->id)->lockForUpdate()->firstOrFail();
            $balance = $this->currentBalance($item->id);

            $signed = match ($type) {
                StockMovementType::In, StockMovementType::Opening => $quantity,
                StockMovementType::Out => -$quantity,
                StockMovementType::Adjustment => $quantity,
            };

            $next = $balance + $signed;
            if ($next < 0) {
                throw new BusinessRuleException('Stok tidak boleh negatif.');
            }

            $tx = StockTransaction::query()->create([
                'item_id' => $item->id,
                'type' => $type,
                'quantity' => abs($quantity),
                'balance_after' => $next,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'division_id' => $divisionId,
                'notes' => $notes,
                'created_by' => $actor->id,
            ]);

            $this->audit->log($actor, 'stock.'.$type->value, $tx, ['balance' => $balance], ['balance' => $next], $notes);

            if (setting('min_stock_notification') && $next <= (float) $item->min_stock) {
                app(NotificationService::class)->notifyRole(
                    'ga',
                    'Stok minimum',
                    "Stok {$item->name} mencapai {$next} (minimum {$item->min_stock}).",
                    route('stock.index')
                );
            }

            return $tx;
        });
    }

    public function adjust(User $actor, Item $item, float $delta, string $reason): StockAdjustment
    {
        if ($reason === '') {
            throw new BusinessRuleException('Alasan penyesuaian stok wajib diisi.');
        }

        return DB::transaction(function () use ($actor, $item, $delta, $reason) {
            $adjustment = StockAdjustment::query()->create([
                'item_id' => $item->id,
                'quantity_delta' => $delta,
                'reason' => $reason,
                'created_by' => $actor->id,
            ]);

            $type = StockMovementType::Adjustment;
            $qty = abs($delta);
            if ($delta < 0) {
                $this->move($actor, $item, StockMovementType::Out, $qty, $adjustment, $reason);
            } elseif ($delta > 0) {
                $this->move($actor, $item, $type, $qty, $adjustment, $reason);
            }

            return $adjustment;
        });
    }
}
