<?php

namespace App\Services;

use App\Models\GoodsReceiptItem;
use App\Models\RequisitionItem;
use App\Models\StockTransaction;
use Illuminate\Support\Carbon;

class UsageStatsService
{
    public function previousMonthUsage(int $itemId, int $year, int $month): float
    {
        $date = Carbon::create($year, $month, 1)->subMonth();

        return (float) RequisitionItem::query()
            ->where('item_id', $itemId)
            ->whereNotNull('quantity_sm_approved')
            ->whereHas('requisition.period', function ($q) use ($date) {
                $q->where('year', $date->year)->where('month', $date->month);
            })
            ->sum('quantity_sm_approved');
    }

    public function averageMonthlyUsage(int $itemId, int $months = 6): float
    {
        $from = now()->subMonths($months);

        $total = (float) StockTransaction::query()
            ->where('item_id', $itemId)
            ->where('type', 'out')
            ->where('created_at', '>=', $from)
            ->sum('quantity');

        return round($total / max($months, 1), 2);
    }

    public function receivedThisPeriod(int $itemId, int $year, int $month): float
    {
        return (float) GoodsReceiptItem::query()
            ->where('item_id', $itemId)
            ->whereHas('receipt', function ($q) use ($year, $month) {
                $q->whereYear('received_at', $year)->whereMonth('received_at', $month);
            })
            ->sum('quantity_received');
    }
}
