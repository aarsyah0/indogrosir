<?php

namespace App\Services;

use App\Enums\ProcurementStatus;
use App\Enums\ReceiptStatus;
use App\Enums\RequisitionStatus;
use App\Models\Item;
use App\Models\ItemPriceHistory;
use App\Models\ProcurementOrder;
use App\Models\Requisition;
use App\Models\StockTransaction;
use App\Models\RequestPeriod;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function for(User $user): array
    {
        if ($user->hasRole('admin')) {
            return $this->admin();
        }
        if ($user->hasPermission('approvals.ga') || $user->hasRole('ga')) {
            return $this->ga($user);
        }
        if ($user->hasPermission('approvals.manager')) {
            return $this->manager($user);
        }
        if ($user->hasPermission('approvals.sam') || $user->hasPermission('approvals.sm')) {
            return $this->executive($user);
        }

        return $this->staff($user);
    }

    private function admin(): array
    {
        $waitingStatuses = [
            RequisitionStatus::PendingManager,
            RequisitionStatus::PendingGa,
            RequisitionStatus::PendingSam,
            RequisitionStatus::PendingSm,
        ];

        return [
            'role' => 'admin',
            'cards' => [
                ['label' => 'Pengajuan tercatat', 'value' => Requisition::query()->count()],
                ['label' => 'Menunggu persetujuan', 'value' => Requisition::query()->whereIn('status', $waitingStatuses)->count()],
                ['label' => 'Barang aktif', 'value' => Item::query()->where('is_active', true)->count()],
                ['label' => 'Periode terbuka', 'value' => RequestPeriod::query()->where('status', 'open')->count()],
            ],
            'list' => Requisition::query()->with('division')->latest()->limit(10)->get(),
        ];
    }

    private function ga(User $user): array
    {
        $monthStart = now()->startOfMonth();

        return [
            'role' => 'ga',
            'cards' => [
                ['label' => 'Pengajuan masuk', 'value' => Requisition::query()->where('status', '!=', RequisitionStatus::Draft)->count()],
                ['label' => 'Menunggu verifikasi GA', 'value' => Requisition::query()->where('status', RequisitionStatus::PendingGa)->count()],
                ['label' => 'Menunggu SAM', 'value' => Requisition::query()->where('status', RequisitionStatus::PendingSam)->count()],
                ['label' => 'Menunggu SM', 'value' => Requisition::query()->where('status', RequisitionStatus::PendingSm)->count()],
                ['label' => 'Siap dibeli', 'value' => Requisition::query()->where('status', RequisitionStatus::ApprovedForPurchase)->where('procurement_status', ProcurementStatus::NotPurchased)->count()],
                ['label' => 'Pembelian berjalan', 'value' => Requisition::query()->where('procurement_status', ProcurementStatus::Partial)->count()],
                ['label' => 'Penerimaan belum selesai', 'value' => Requisition::query()->whereIn('receipt_status', [ReceiptStatus::Awaiting, ReceiptStatus::Partial, ReceiptStatus::NeedsInspection])->count()],
                ['label' => 'Pengeluaran bulan ini', 'value' => format_money(ProcurementOrder::query()->where('purchased_at', '>=', $monthStart)->sum('actual_total'))],
            ],
            'lowStock' => Item::query()->where('is_active', true)->get()->filter(fn (Item $i) => $i->currentStock() <= (float) $i->min_stock)->take(8)->values(),
            'priceIncreases' => $this->priceIncreases(),
            'recent' => Requisition::query()->latest()->with('division')->limit(8)->get(),
            'monthlyNeeds' => $this->monthlyNeeds(),
        ];
    }

    private function manager(User $user): array
    {
        $divisionIds = $user->managedDivisions()->pluck('divisions.id');

        return [
            'role' => 'manager',
            'cards' => [
                ['label' => 'Menunggu approval', 'value' => Requisition::query()->whereIn('division_id', $divisionIds)->where('status', RequisitionStatus::PendingManager)->count()],
                ['label' => 'Revisi', 'value' => Requisition::query()->whereIn('division_id', $divisionIds)->where('status', RequisitionStatus::RevisionDivision)->count()],
                ['label' => 'Total kebutuhan divisi', 'value' => format_money(Requisition::query()->whereIn('division_id', $divisionIds)->sum('estimated_total'))],
            ],
            'history' => $user->loadMissing('roles') ? \App\Models\ApprovalHistory::query()->where('actor_id', $user->id)->latest()->limit(10)->with('requisition')->get() : collect(),
            'comparison' => $this->divisionMonthly($divisionIds->first()),
            'list' => Requisition::query()->whereIn('division_id', $divisionIds)->latest()->limit(10)->get(),
        ];
    }

    private function executive(User $user): array
    {
        $pending = $user->hasPermission('approvals.sam')
            ? RequisitionStatus::PendingSam
            : RequisitionStatus::PendingSm;

        return [
            'role' => 'executive',
            'cards' => [
                ['label' => 'Menunggu persetujuan', 'value' => Requisition::query()->where('status', $pending)->count()],
                ['label' => 'Estimasi menunggu', 'value' => format_money(Requisition::query()->where('status', $pending)->sum('estimated_total'))],
                ['label' => 'Dikembalikan', 'value' => Requisition::query()->where('status', RequisitionStatus::RevisionSamSm)->count()],
                ['label' => 'Pengeluaran bulan ini', 'value' => format_money(ProcurementOrder::query()->where('purchased_at', '>=', now()->startOfMonth())->sum('actual_total'))],
            ],
            'history' => \App\Models\ApprovalHistory::query()->where('actor_id', $user->id)->latest()->limit(10)->with('requisition')->get(),
            'queue' => Requisition::query()->where('status', $pending)->with('division')->latest()->get(),
        ];
    }

    private function staff(User $user): array
    {
        $mine = Requisition::query()->where('division_id', $user->division_id);

        return [
            'role' => 'staff',
            'cards' => [
                ['label' => 'Pengajuan saya', 'value' => (clone $mine)->where('requester_id', $user->id)->count()],
                ['label' => 'Dalam proses', 'value' => (clone $mine)->whereNotIn('status', [RequisitionStatus::Draft, RequisitionStatus::RejectedManager, RequisitionStatus::Cancelled])->count()],
                ['label' => 'Perlu revisi', 'value' => (clone $mine)->where('status', RequisitionStatus::RevisionDivision)->count()],
                ['label' => 'Selesai approval', 'value' => (clone $mine)->where('status', RequisitionStatus::ApprovedForPurchase)->count()],
            ],
            'activePeriod' => \App\Models\RequestPeriod::query()->where('status', 'open')->latest()->first(),
            'list' => (clone $mine)->latest()->limit(10)->get(),
        ];
    }

    private function priceIncreases(): array
    {
        $items = Item::query()->where('is_active', true)->get();
        $out = [];
        foreach ($items as $item) {
            $hist = ItemPriceHistory::query()->where('item_id', $item->id)->orderByDesc('recorded_at')->limit(2)->get();
            if ($hist->count() < 2) {
                continue;
            }
            $new = (float) $hist[0]->price;
            $old = (float) $hist[1]->price;
            if ($old <= 0 || $new <= $old) {
                continue;
            }
            $out[] = ['item' => $item, 'old' => $old, 'new' => $new, 'pct' => round((($new - $old) / $old) * 100, 1)];
        }

        return $out;
    }

    private function monthlyNeeds(): array
    {
        $rows = Requisition::query()
            ->selectRaw('request_periods.month, request_periods.year, SUM(requisitions.estimated_total) as total')
            ->join('request_periods', 'request_periods.id', '=', 'requisitions.request_period_id')
            ->groupBy('request_periods.year', 'request_periods.month')
            ->orderBy('request_periods.year')
            ->orderBy('request_periods.month')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => sprintf('%02d/%d', $r->month, $r->year))->all(),
            'data' => $rows->pluck('total')->map(fn ($v) => (float) $v)->all(),
        ];
    }

    private function divisionMonthly(?int $divisionId): array
    {
        if (! $divisionId) {
            return ['labels' => [], 'data' => []];
        }

        $rows = Requisition::query()
            ->selectRaw('request_periods.month, request_periods.year, SUM(requisitions.estimated_total) as total')
            ->join('request_periods', 'request_periods.id', '=', 'requisitions.request_period_id')
            ->where('requisitions.division_id', $divisionId)
            ->groupBy('request_periods.year', 'request_periods.month')
            ->orderBy('request_periods.year')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => sprintf('%02d/%d', $r->month, $r->year))->all(),
            'data' => $rows->pluck('total')->map(fn ($v) => (float) $v)->all(),
        ];
    }
}
