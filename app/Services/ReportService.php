<?php

namespace App\Services;

use App\Enums\RequisitionStatus;
use App\Models\GoodsReceiptItem;
use App\Models\Item;
use App\Models\ItemPriceHistory;
use App\Models\ProcurementOrder;
use App\Models\ProcurementOrderItem;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function monthly(User $user, array $filters): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = (int) ($filters['month'] ?? now()->month);
        $divisionId = $filters['division_id'] ?? null;
        $categoryId = $filters['item_category_id'] ?? null;
        $itemId = $filters['item_id'] ?? null;
        $status = $filters['status'] ?? null;
        $supplierId = $filters['supplier_id'] ?? null;

        if (! $user->hasPermission('requisitions.view_all')) {
            $divisionId = $user->division_id ?: -1;
        }

        $reqs = Requisition::query()
            ->whereHas('period', fn ($q) => $q->where('year', $year)->where('month', $month))
            ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['division', 'latestVersionRecord', 'approvalHistories.actor']);

        $requisitions = $reqs->get();

        $lines = RequisitionItem::query()
            ->whereIn('requisition_id', $requisitions->pluck('id'))
            ->whereIn('requisition_version_id', $requisitions->map(fn ($r) => $r->latestVersion()?->id)->filter())
            ->when($itemId, fn ($q) => $q->where('item_id', $itemId))
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereHas('item', fn ($i) => $i->where('item_category_id', $categoryId));
            })
            ->with('requisition.division')
            ->get();

        $bought = ProcurementOrderItem::query()
            ->whereHas('order', function ($q) use ($requisitions, $supplierId) {
                $q->whereIn('requisition_id', $requisitions->pluck('id'))
                    ->when($supplierId, fn ($qq) => $qq->where('supplier_id', $supplierId));
            })
            ->when($itemId, fn ($q) => $q->where('item_id', $itemId))
            ->when($categoryId, fn ($q) => $q->whereHas('item', fn ($item) => $item->where('item_category_id', $categoryId)))
            ->get();

        $received = GoodsReceiptItem::query()
            ->whereHas('receipt.order', function ($q) use ($requisitions, $supplierId) {
                $q->whereIn('requisition_id', $requisitions->pluck('id'))
                    ->when($supplierId, fn ($order) => $order->where('supplier_id', $supplierId));
            })
            ->when($itemId, fn ($q) => $q->where('item_id', $itemId))
            ->when($categoryId, fn ($q) => $q->whereHas('item', fn ($item) => $item->where('item_category_id', $categoryId)))
            ->get();

        $estimate = (float) $lines->sum(fn ($l) => (float) ($l->quantity_ga_final ?? $l->quantity_requested) * (float) $l->reference_unit_price);
        if ($itemId || $categoryId) {
            $actual = (float) $bought->sum('subtotal');
        } else {
            $actual = (float) ProcurementOrder::query()
                ->whereIn('requisition_id', $requisitions->pluck('id'))
                ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
                ->sum('actual_total');
        }

        $prev = $this->previousNeed($year, $month, $divisionId);

        return [
            'filters' => compact('year', 'month', 'divisionId', 'categoryId', 'itemId', 'status', 'supplierId'),
            'total_requisitions' => $requisitions->count(),
            'approved' => $requisitions->where('status', RequisitionStatus::ApprovedForPurchase)->count(),
            'rejected' => $requisitions->where('status', RequisitionStatus::RejectedManager)->count(),
            'qty_requested' => (float) $lines->sum('quantity_requested'),
            'qty_approved' => (float) $lines->sum('quantity_sm_approved'),
            'qty_purchased' => (float) $bought->sum('quantity_purchased'),
            'qty_received' => (float) $received->sum('quantity_received'),
            'estimate' => $estimate,
            'actual' => $actual,
            'variance' => $actual - $estimate,
            'by_division' => $lines->groupBy(fn ($line) => $line->requisition?->division_id)->map(function (Collection $group) {
                return [
                    'division' => $group->first()->requisition?->division?->name,
                    'count' => $group->pluck('requisition_id')->unique()->count(),
                    'estimate' => (float) $group->sum(fn ($line) => (float) ($line->quantity_ga_final ?? $line->quantity_requested) * (float) $line->reference_unit_price),
                ];
            })->values(),
            'top_items' => $lines->groupBy('item_id')->map(function (Collection $group) {
                return [
                    'name' => $group->first()->item_name_snapshot,
                    'qty' => (float) $group->sum('quantity_requested'),
                ];
            })->sortByDesc('qty')->take(10)->values(),
            'need_change' => $prev === null ? null : [
                'previous' => $prev,
                'current' => (float) $lines->sum('quantity_requested'),
            ],
            'price_changes' => $this->priceChanges($year, $month, $itemId, $categoryId),
            'ending_stock' => Item::query()->where('is_active', true)->get()->map(fn (Item $i) => [
                'name' => $i->name,
                'stock' => $i->currentStock(),
            ]),
            'open_requisitions' => $requisitions->filter(fn ($r) => ! $r->status->isTerminal() && $r->status !== RequisitionStatus::ApprovedForPurchase)->values(),
            'approvals' => $requisitions->flatMap->approvalHistories,
            'requisitions' => $requisitions,
        ];
    }

    public function recap(User $user, array $filters): Collection
    {
        $data = $this->monthly($user, $filters);
        $rows = collect();
        $supplierId = $filters['supplier_id'] ?? null;
        $itemId = $filters['item_id'] ?? null;
        $categoryId = $filters['item_category_id'] ?? null;
        $requisitions = $data['requisitions'];

        if ($supplierId) {
            $requisitions = $requisitions->filter(fn (Requisition $requisition) =>
                $requisition->procurementOrders()->where('supplier_id', $supplierId)->exists()
            );
        }

        foreach ($requisitions as $req) {
            foreach ($req->latestItems()->with('item')->get() as $line) {
                if ($itemId && (int) $line->item_id !== (int) $itemId) {
                    continue;
                }
                if ($categoryId && (int) $line->item?->item_category_id !== (int) $categoryId) {
                    continue;
                }

                $purchaseLines = ProcurementOrderItem::query()
                    ->where('item_id', $line->item_id)
                    ->whereHas('order', function ($q) use ($req, $supplierId) {
                        $q->where('requisition_id', $req->id)
                            ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId));
                    });
                $purchased = (float) (clone $purchaseLines)->sum('quantity_purchased');
                $received = (float) GoodsReceiptItem::query()
                    ->where('item_id', $line->item_id)
                    ->whereHas('receipt.order', function ($q) use ($req, $supplierId) {
                        $q->where('requisition_id', $req->id)
                            ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId));
                    })
                    ->sum('quantity_received');
                $avgActual = (float) (clone $purchaseLines)->avg('unit_price');
                $rows->push([
                    'requisition' => $req,
                    'line' => $line,
                    'purchased' => $purchased,
                    'received' => $received,
                    'short' => max(((float) ($line->quantity_sm_approved ?? 0)) - $received, 0),
                    'actual_price' => $avgActual,
                    'estimate' => (float) $line->estimated_subtotal,
                    'actual_cost' => $purchased * $avgActual,
                    'status' => $this->fitStatus($req, $line, $purchased, $received, $avgActual),
                ]);
            }
        }

        return $rows;
    }

    public function charts(array $filters, User $user): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $divisionId = $filters['division_id'] ?? null;
        $categoryId = $filters['item_category_id'] ?? null;
        $itemId = $filters['item_id'] ?? null;

        if (! $user->hasPermission('requisitions.view_all')) {
            $divisionId = $user->division_id ?: -1;
        }

        $usage = StockTransaction::query()
            ->selectRaw($this->monthExpression('created_at').' as m, SUM(quantity) as qty')
            ->where('type', 'out')
            ->whereYear('created_at', $year)
            ->when($divisionId, fn ($q, $id) => $q->where('division_id', $id))
            ->when($itemId, fn ($q, $id) => $q->where('item_id', $id))
            ->when($categoryId, fn ($q, $id) => $q->whereHas('item', fn ($item) => $item->where('item_category_id', $id)))
            ->groupBy('m')
            ->pluck('qty', 'm');

        if ($itemId || $categoryId) {
            $spend = ProcurementOrderItem::query()
                ->join('procurement_orders', 'procurement_orders.id', '=', 'procurement_order_items.procurement_order_id')
                ->selectRaw($this->monthExpression('procurement_orders.purchased_at').' as m, SUM(procurement_order_items.subtotal) as total')
                ->whereYear('procurement_orders.purchased_at', $year)
                ->when($divisionId, fn ($q, $id) => $q->whereHas('order.requisition', fn ($requisition) => $requisition->where('division_id', $id)))
                ->when($itemId, fn ($q, $id) => $q->where('procurement_order_items.item_id', $id))
                ->when($categoryId, fn ($q, $id) => $q->whereHas('item', fn ($item) => $item->where('item_category_id', $id)))
                ->groupBy('m')
                ->pluck('total', 'm');
        } else {
            $spend = ProcurementOrder::query()
                ->selectRaw($this->monthExpression('purchased_at').' as m, SUM(actual_total) as total')
                ->whereYear('purchased_at', $year)
                ->when($divisionId, fn ($q, $id) => $q->whereHas('requisition', fn ($requisition) => $requisition->where('division_id', $id)))
                ->groupBy('m')
                ->pluck('total', 'm');
        }

        if ($itemId || $categoryId) {
            $byDivision = ProcurementOrderItem::query()
                ->join('procurement_orders', 'procurement_orders.id', '=', 'procurement_order_items.procurement_order_id')
                ->join('requisitions', 'requisitions.id', '=', 'procurement_orders.requisition_id')
                ->join('divisions', 'divisions.id', '=', 'requisitions.division_id')
                ->selectRaw('divisions.name, SUM(procurement_order_items.subtotal) as total')
                ->whereYear('procurement_orders.purchased_at', $year)
                ->when($divisionId, fn ($q, $id) => $q->where('requisitions.division_id', $id))
                ->when($itemId, fn ($q, $id) => $q->where('procurement_order_items.item_id', $id))
                ->when($categoryId, fn ($q, $id) => $q->whereHas('item', fn ($item) => $item->where('item_category_id', $id)))
                ->groupBy('divisions.name')
                ->get();
        } else {
            $byDivision = ProcurementOrder::query()
            ->join('requisitions', 'requisitions.id', '=', 'procurement_orders.requisition_id')
            ->join('divisions', 'divisions.id', '=', 'requisitions.division_id')
            ->selectRaw('divisions.name, SUM(procurement_orders.actual_total) as total')
            ->whereYear('procurement_orders.purchased_at', $year)
            ->when($divisionId, fn ($q, $id) => $q->where('requisitions.division_id', $id))
            ->groupBy('divisions.name')
            ->get();
        }

        $byCategory = ProcurementOrderItem::query()
            ->join('procurement_orders', 'procurement_orders.id', '=', 'procurement_order_items.procurement_order_id')
            ->join('items', 'items.id', '=', 'procurement_order_items.item_id')
            ->join('item_categories', 'item_categories.id', '=', 'items.item_category_id')
            ->selectRaw('item_categories.name, SUM(procurement_order_items.subtotal) as total')
            ->whereYear('procurement_orders.purchased_at', $year)
            ->when($divisionId, fn ($q, $id) => $q->whereHas('order.requisition', fn ($requisition) => $requisition->where('division_id', $id)))
            ->when($itemId, fn ($q, $id) => $q->where('procurement_order_items.item_id', $id))
            ->when($categoryId, fn ($q, $id) => $q->where('items.item_category_id', $id))
            ->groupBy('item_categories.name')
            ->get();

        $requisitionScope = fn ($query) => $query
            ->whereHas('period', fn ($period) => $period->where('year', $year))
            ->when($divisionId, fn ($requisitions, $id) => $requisitions->where('division_id', $id));
        $latestVersionIds = DB::table('requisition_versions')
            ->join('requisitions', 'requisitions.id', '=', 'requisition_versions.requisition_id')
            ->join('request_periods', 'request_periods.id', '=', 'requisitions.request_period_id')
            ->whereColumn('requisition_versions.version_number', 'requisitions.current_version')
            ->where('request_periods.year', $year)
            ->when($divisionId, fn ($q, $id) => $q->where('requisitions.division_id', $id))
            ->select('requisition_versions.id');

        $compare = [
            'requested' => (float) RequisitionItem::query()
                ->whereIn('requisition_version_id', $latestVersionIds)
                ->sum('quantity_requested'),
            'approved' => (float) RequisitionItem::query()
                ->whereIn('requisition_version_id', $latestVersionIds)
                ->sum('quantity_sm_approved'),
            'purchased' => (float) ProcurementOrderItem::query()
                ->whereHas('order', fn ($order) => $order->whereYear('purchased_at', $year)
                    ->when($divisionId, fn ($orders, $id) => $orders->whereHas('requisition', fn ($requisition) => $requisition->where('division_id', $id))))
                ->sum('quantity_purchased'),
            'received' => (float) GoodsReceiptItem::query()
                ->whereHas('receipt', fn ($receipt) => $receipt->whereYear('received_at', $year)
                    ->whereHas('order.requisition', fn ($requisition) => $requisition->when($divisionId, fn ($query, $id) => $query->where('division_id', $id))))
                ->sum('quantity_received'),
        ];

        return [
            'usage' => $this->twelve($usage),
            'spend' => $this->twelve($spend),
            'byDivision' => $byDivision,
            'byCategory' => $byCategory,
            'compare' => $compare,
            'prices' => ItemPriceHistory::query()
                ->whereYear('recorded_at', $year)
                ->when($filters['item_id'] ?? null, fn ($q, $id) => $q->where('item_id', $id))
                ->when($categoryId, fn ($q) => $q->whereHas('item', fn ($item) => $item->where('item_category_id', $categoryId)))
                ->orderByDesc('recorded_at')
                ->with('item')
                ->limit(50)
                ->get(),
            'estimateVsActual' => [
                'estimate' => (float) Requisition::query()
                    ->whereHas('period', fn ($q) => $q->where('year', $year))
                    ->when($divisionId, fn ($q, $id) => $q->where('division_id', $id))
                    ->sum('estimated_total'),
                'actual' => (float) ProcurementOrder::query()
                    ->whereYear('purchased_at', $year)
                    ->when($divisionId, fn ($q, $id) => $q->whereHas('requisition', fn ($r) => $r->where('division_id', $id)))
                    ->sum('actual_total'),
            ],
        ];
    }

    private function twelve($pluck): array
    {
        $out = [];
        for ($i = 1; $i <= 12; $i++) {
            $out[] = (float) ($pluck[$i] ?? 0);
        }

        return $out;
    }

    private function monthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    private function previousNeed(int $year, int $month, ?int $divisionId): ?float
    {
        $date = Carbon::create($year, $month, 1)->subMonth();
        $exists = Requisition::query()
            ->whereHas('period', fn ($q) => $q->where('year', $date->year)->where('month', $date->month))
            ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
            ->exists();
        if (! $exists) {
            return null;
        }

        return (float) RequisitionItem::query()
            ->whereHas('requisition.period', fn ($q) => $q->where('year', $date->year)->where('month', $date->month))
            ->when($divisionId, fn ($q) => $q->whereHas('requisition', fn ($r) => $r->where('division_id', $divisionId)))
            ->sum('quantity_requested');
    }

    private function priceChanges(int $year, int $month, ?int $itemId, ?int $categoryId): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $items = Item::query()
            ->when($itemId, fn ($q) => $q->whereKey($itemId))
            ->when($categoryId, fn ($q) => $q->where('item_category_id', $categoryId))
            ->get();
        $changes = [];
        foreach ($items as $item) {
            $previous = $item->priceHistories()
                ->where('recorded_at', '<', $start)
                ->orderByDesc('recorded_at')
                ->first();
            $current = $item->priceHistories()
                ->whereBetween('recorded_at', [$start, $end])
                ->orderByDesc('recorded_at')
                ->first();
            if (! $previous || ! $current) {
                continue;
            }
            $old = (float) $previous->price;
            $new = (float) $current->price;
            if ($old <= 0 || $old === $new) {
                continue;
            }
            $changes[] = [
                'item' => $item->name,
                'from' => $old,
                'to' => $new,
                'pct' => round((($new - $old) / $old) * 100, 1),
            ];
        }

        return $changes;
    }

    private function fitStatus($req, $line, float $purchased, float $received, float $avgActual): string
    {
        $approved = (float) ($line->quantity_sm_approved ?? 0);
        if ($purchased <= 0) {
            return 'Menunggu Penerimaan';
        }
        if ($avgActual > 0 && abs($avgActual - (float) $line->reference_unit_price) > 0.01) {
            return 'Harga Berubah';
        }
        if ($received + 0.0001 < $purchased) {
            return $req->receipt_status === \App\Enums\ReceiptStatus::NeedsInspection ? 'Perlu Pemeriksaan' : 'Pembelian Parsial';
        }
        if ($approved > 0 && $received + 0.0001 < $approved) {
            return 'Kurang Kuantitas';
        }
        if ($received + 0.0001 >= $approved && $approved > 0) {
            return 'Sesuai';
        }

        return 'Menunggu Penerimaan';
    }
}
