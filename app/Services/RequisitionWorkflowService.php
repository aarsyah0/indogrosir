<?php

namespace App\Services;

use App\Enums\ApprovalAction;
use App\Enums\RequisitionStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\ApprovalHistory;
use App\Models\ApprovalStep;
use App\Models\Item;
use App\Models\RequestPeriod;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RequisitionWorkflowService
{
    public function __construct(
        private AuditService $audit,
        private DocumentNumberService $numbers,
        private NotificationService $notifications,
        private UsageStatsService $usage,
    ) {}

    public function save(User $user, array $payload, ?Requisition $requisition = null): Requisition
    {
        $this->assertStaffCanWrite($user, $requisition);
        $period = RequestPeriod::query()->findOrFail($payload['request_period_id']);
        if (! $period->isOpen() && (! $requisition || $requisition->status === RequisitionStatus::Draft)) {
            throw new BusinessRuleException('Periode pengajuan tidak aktif.');
        }

        $items = $this->normalizeItems($payload['items'] ?? []);

        return DB::transaction(function () use ($user, $payload, $requisition, $period, $items) {
            if (! $requisition) {
                $requisition = Requisition::query()->create([
                    'number' => $this->numbers->next('REQ'),
                    'request_period_id' => $period->id,
                    'division_id' => $user->division_id,
                    'requester_id' => $user->id,
                    'status' => RequisitionStatus::Draft,
                    'notes' => $payload['notes'] ?? null,
                    'current_version' => 1,
                    'estimated_total' => 0,
                ]);
                $version = RequisitionVersion::query()->create([
                    'requisition_id' => $requisition->id,
                    'version_number' => 1,
                    'created_by' => $user->id,
                    'reason' => 'Draft awal',
                    'estimated_total' => 0,
                ]);
            } else {
                $this->assertEditableByStaff($requisition);
                $version = $requisition->latestVersion();
                $needsNewVersion = $requisition->status === RequisitionStatus::RevisionDivision
                    && $version
                    && $requisition->approvalHistories()
                        ->where('requisition_version_id', $version->id)
                        ->where('step', 'manager')
                        ->exists();
                if (! $version || $version->isLocked() || $needsNewVersion) {
                    $version = $this->spawnVersion($requisition, $user, 'Perbaikan pengajuan');
                }
                $requisition->update(['notes' => $payload['notes'] ?? $requisition->notes]);
            }

            $this->replaceVersionItems($requisition, $version, $items);
            $this->audit->log($user, 'requisition.save', $requisition, null, $requisition->fresh()->toArray());

            return $requisition->fresh(['latestVersionRecord', 'division']);
        });
    }

    public function submit(User $user, Requisition $requisition): Requisition
    {
        $this->assertStaffCanWrite($user, $requisition);
        $this->assertEditableByStaff($requisition);

        if (! $requisition->period->isOpen() && $requisition->status === RequisitionStatus::Draft) {
            throw new BusinessRuleException('Periode pengajuan tidak aktif.');
        }

        $version = $requisition->latestVersion();
        if (! $version || $version->items()->count() === 0) {
            throw new BusinessRuleException('Pengajuan harus memiliki minimal satu barang.');
        }

        return DB::transaction(function () use ($user, $requisition, $version) {
            $from = $requisition->status;
            $requisition->update([
                'status' => RequisitionStatus::PendingManager,
                'submitted_at' => now(),
                'estimated_total' => $version->estimated_total,
            ]);
            $this->ensureStep($requisition, $version, 'manager');
            $this->recordHistory($user, $requisition, $version, 'staff', 'submit', ApprovalAction::Approved, 'Pengajuan dikirim');
            $this->audit->log($user, 'requisition.submit', $requisition, ['status' => $from], ['status' => $requisition->status]);

            $managers = $requisition->division->activeManagers;
            $this->notifications->notifyUsers(
                $managers,
                'Pengajuan baru menunggu approval',
                "Pengajuan {$requisition->number} dari {$requisition->division->name} menunggu keputusan Anda.",
                route('approvals.manager.show', $requisition)
            );

            return $requisition->fresh();
        });
    }

    public function managerDecide(User $user, Requisition $requisition, ApprovalAction $action, ?string $comment): Requisition
    {
        if (! $user->hasPermission('approvals.manager') || ! $user->managesDivision($requisition->division_id)) {
            throw new BusinessRuleException('Anda tidak berwenang menyetujui divisi ini.');
        }
        if ($user->id === $requisition->requester_id) {
            throw new BusinessRuleException('Requester tidak dapat melakukan self-approval.');
        }
        if ($requisition->status !== RequisitionStatus::PendingManager) {
            throw new BusinessRuleException('Pengajuan tidak menunggu approval Manager.');
        }
        if (in_array($action, [ApprovalAction::Returned, ApprovalAction::Rejected], true) && blank($comment)) {
            throw new BusinessRuleException('Alasan wajib diisi.');
        }

        return DB::transaction(function () use ($user, $requisition, $action, $comment) {
            $version = $requisition->latestVersion();
            $from = $requisition->status;

            if ($action === ApprovalAction::Approved) {
                foreach ($version->items as $item) {
                    $item->update(['quantity_manager_approved' => $item->quantity_requested]);
                }
                $requisition->update(['status' => RequisitionStatus::PendingGa]);
                $this->closeStep($requisition, $version, 'manager', $user, 'approved', $comment);
                $this->ensureStep($requisition, $version, 'ga');
                $this->notifications->notifyRole(
                    'ga',
                    'Pengajuan siap diverifikasi',
                    "Pengajuan {$requisition->number} telah disetujui Manager.",
                    route('approvals.ga.show', $requisition)
                );
            } elseif ($action === ApprovalAction::Returned) {
                $requisition->update(['status' => RequisitionStatus::RevisionDivision]);
                $this->closeStep($requisition, $version, 'manager', $user, 'returned', $comment);
                $this->notifications->notifyUsers(
                    [$requisition->requester],
                    'Pengajuan dikembalikan untuk revisi',
                    $comment,
                    route('requisitions.show', $requisition)
                );
            } else {
                $requisition->update(['status' => RequisitionStatus::RejectedManager]);
                $this->closeStep($requisition, $version, 'manager', $user, 'rejected', $comment);
                $this->notifications->notifyUsers(
                    [$requisition->requester],
                    'Pengajuan ditolak Manager',
                    $comment,
                    route('requisitions.show', $requisition)
                );
            }

            $this->recordHistory($user, $requisition, $version, 'manager', 'manager', $action, $comment);
            $this->audit->log($user, 'approval.manager.'.$action->value, $requisition, ['status' => $from], ['status' => $requisition->status], $comment);

            return $requisition->fresh();
        });
    }

    public function gaUpdate(User $user, Requisition $requisition, array $lines, ?string $returnComment = null, bool $submitToSam = false, bool $returnToDivision = false): Requisition
    {
        if (! $user->hasPermission('approvals.ga')) {
            throw new BusinessRuleException('Tidak berwenang melakukan verifikasi GA.');
        }
        if (! in_array($requisition->status, [
            RequisitionStatus::PendingGa,
            RequisitionStatus::RevisionGa,
            RequisitionStatus::RevisionSamSm,
        ], true)) {
            throw new BusinessRuleException('Pengajuan tidak berada pada tahap verifikasi GA.');
        }

        return DB::transaction(function () use ($user, $requisition, $lines, $returnComment, $submitToSam, $returnToDivision) {
            $version = $this->editableGaVersion($requisition, $user);
            $from = $requisition->status;

            foreach ($lines as $line) {
                /** @var RequisitionItem $item */
                $item = $version->items()->where('id', $line['id'])->firstOrFail();
                $newQty = (float) $line['quantity_ga_final'];
                $oldQty = $item->quantity_ga_final !== null
                    ? (float) $item->quantity_ga_final
                    : (float) ($item->quantity_manager_approved ?? $item->quantity_requested);

                if (abs($newQty - $oldQty) > 0.0001 && blank($line['ga_change_reason'] ?? null)) {
                    throw new BusinessRuleException('Alasan perubahan kuantitas wajib diisi untuk '.$item->item_name_snapshot.'.');
                }

                $log = $item->quantity_change_log ?? [];
                if (abs($newQty - $oldQty) > 0.0001) {
                    $log[] = [
                        'before' => $oldQty,
                        'after' => $newQty,
                        'reason' => $line['ga_change_reason'],
                        'user_id' => $user->id,
                        'at' => now()->toIso8601String(),
                    ];
                }

                $item->update([
                    'quantity_ga_recommended' => $line['quantity_ga_recommended'] ?? $newQty,
                    'quantity_ga_final' => $newQty,
                    'ga_change_reason' => $line['ga_change_reason'] ?? $item->ga_change_reason,
                    'estimated_subtotal' => $newQty * (float) $item->reference_unit_price,
                    'quantity_change_log' => $log,
                ]);
            }

            $total = (float) $version->items()->sum('estimated_subtotal');
            $version->update(['estimated_total' => $total]);
            $requisition->update(['estimated_total' => $total]);

            if ($returnToDivision) {
                if (blank($returnComment)) {
                    throw new BusinessRuleException('Catatan pengembalian wajib diisi.');
                }
                $requisition->update(['status' => RequisitionStatus::RevisionDivision]);
                $this->recordHistory($user, $requisition, $version, 'ga', 'ga', ApprovalAction::Returned, $returnComment);
                $this->notifications->notifyUsers([$requisition->requester], 'GA mengembalikan pengajuan', $returnComment, route('requisitions.show', $requisition));
            } elseif ($submitToSam) {
                foreach ($version->items as $item) {
                    if ($item->quantity_ga_final === null) {
                        throw new BusinessRuleException('Semua barang harus memiliki kuantitas final GA.');
                    }
                }
                $version->update(['locked_at' => now()]);
                $requisition->update(['status' => RequisitionStatus::PendingSam]);
                $this->closeStep($requisition, $version, 'ga', $user, 'approved', 'Versi final dikirim ke SAM');
                $this->ensureStep($requisition, $version, 'sam');
                $this->recordHistory($user, $requisition, $version, 'ga', 'ga', ApprovalAction::Approved, 'Dikirim ke SAM');
                $this->notifications->notifyRole('sam', 'Pengajuan menunggu SAM', "Pengajuan {$requisition->number} menunggu persetujuan.", route('approvals.sam.show', $requisition));
            }

            $this->audit->log($user, 'verification.ga', $requisition, ['status' => $from], ['status' => $requisition->fresh()->status]);

            return $requisition->fresh();
        });
    }

    public function samDecide(User $user, Requisition $requisition, ApprovalAction $action, ?string $comment): Requisition
    {
        if (! $user->hasPermission('approvals.sam')) {
            throw new BusinessRuleException('Tidak berwenang melakukan approval SAM.');
        }
        if ($requisition->status !== RequisitionStatus::PendingSam) {
            throw new BusinessRuleException('Pengajuan tidak menunggu approval SAM.');
        }
        if ($action === ApprovalAction::Rejected) {
            throw new BusinessRuleException('SAM hanya dapat menyetujui atau mengembalikan untuk revisi.');
        }
        if ($action === ApprovalAction::Returned && blank($comment)) {
            throw new BusinessRuleException('Komentar wajib diisi.');
        }

        return DB::transaction(function () use ($user, $requisition, $action, $comment) {
            $version = $requisition->latestVersion();
            $from = $requisition->status;
            if ($action === ApprovalAction::Approved) {
                foreach ($version->items as $item) {
                    $item->update(['quantity_sam_approved' => $item->quantity_ga_final]);
                }
                $requisition->update(['status' => RequisitionStatus::PendingSm]);
                $this->closeStep($requisition, $version, 'sam', $user, 'approved', $comment);
                $this->ensureStep($requisition, $version, 'sm');
                $this->notifications->notifyRole('sm', 'Pengajuan menunggu SM', "Pengajuan {$requisition->number} menunggu persetujuan SM.", route('approvals.sm.show', $requisition));
            } else {
                $requisition->update(['status' => RequisitionStatus::RevisionSamSm]);
                $this->closeStep($requisition, $version, 'sam', $user, 'returned', $comment);
                $this->notifications->notifyRole('ga', 'Revisi setelah SAM', $comment ?? '', route('approvals.ga.show', $requisition));
            }
            $this->recordHistory($user, $requisition, $version, 'sam', 'sam', $action, $comment);
            $this->audit->log($user, 'approval.sam.'.$action->value, $requisition, ['status' => $from], ['status' => $requisition->status], $comment);

            return $requisition->fresh();
        });
    }

    public function smDecide(User $user, Requisition $requisition, ApprovalAction $action, ?string $comment): Requisition
    {
        if (! $user->hasPermission('approvals.sm')) {
            throw new BusinessRuleException('Tidak berwenang melakukan approval SM.');
        }
        if ($requisition->status !== RequisitionStatus::PendingSm) {
            throw new BusinessRuleException('Pengajuan tidak menunggu approval SM. SAM harus menyetujui lebih dahulu.');
        }
        if ($action === ApprovalAction::Rejected) {
            throw new BusinessRuleException('SM hanya dapat menyetujui atau mengembalikan untuk revisi.');
        }
        if ($action === ApprovalAction::Returned && blank($comment)) {
            throw new BusinessRuleException('Komentar wajib diisi.');
        }

        $samApproved = $requisition->approvalHistories()
            ->where('requisition_version_id', $requisition->latestVersion()?->id)
            ->where('step', 'sam')
            ->where('action', ApprovalAction::Approved->value)
            ->exists();
        if (! $samApproved) {
            throw new BusinessRuleException('SM tidak dapat menyetujui sebelum SAM pada versi yang sama.');
        }

        return DB::transaction(function () use ($user, $requisition, $action, $comment) {
            $version = $requisition->latestVersion();
            $from = $requisition->status;
            if ($action === ApprovalAction::Approved) {
                foreach ($version->items as $item) {
                    $item->update(['quantity_sm_approved' => $item->quantity_ga_final]);
                }
                $requisition->update(['status' => RequisitionStatus::ApprovedForPurchase]);
                $this->closeStep($requisition, $version, 'sm', $user, 'approved', $comment);
                $this->notifications->notifyRole('ga', 'Disetujui untuk pembelian', "Pengajuan {$requisition->number} siap dibeli.", route('procurements.create', ['requisition_id' => $requisition->id]));
            } else {
                $requisition->update(['status' => RequisitionStatus::RevisionSamSm]);
                $this->closeStep($requisition, $version, 'sm', $user, 'returned', $comment);
                $this->notifications->notifyRole('ga', 'Revisi setelah SM', $comment ?? '', route('approvals.ga.show', $requisition));
            }
            $this->recordHistory($user, $requisition, $version, 'sm', 'sm', $action, $comment);
            $this->audit->log($user, 'approval.sm.'.$action->value, $requisition, ['status' => $from], ['status' => $requisition->status], $comment);

            return $requisition->fresh();
        });
    }

    public function verificationContext(Requisition $requisition): array
    {
        $version = $requisition->latestVersion();
        $period = $requisition->period;
        $rows = [];
        foreach ($version?->items ?? [] as $line) {
            $item = $line->item;
            $avg = $this->usage->averageMonthlyUsage($line->item_id);
            $prev = $this->usage->previousMonthUsage($line->item_id, $period->year, $period->month);
            $stock = $item?->currentStock() ?? 0;
            $requested = (float) $line->quantity_requested;
            $multiplier = (float) setting('unreasonable_qty_multiplier', 2);
            $unreasonable = $avg > 0 && $requested > ($avg * $multiplier);

            $rows[] = [
                'line' => $line,
                'stock' => $stock,
                'previous_usage' => $prev,
                'average_usage' => $avg,
                'unreasonable' => $unreasonable,
                'recommended' => $avg > 0 ? max($avg - $stock, 0) : $requested,
            ];
        }

        return $rows;
    }

    private function editableGaVersion(Requisition $requisition, User $user): RequisitionVersion
    {
        $version = $requisition->latestVersion();
        if ($requisition->status === RequisitionStatus::RevisionSamSm || ($version && $version->isLocked() && $requisition->status !== RequisitionStatus::PendingGa)) {
            return $this->spawnVersion($requisition, $user, 'Revisi GA setelah SAM/SM');
        }
        if ($version && $version->isLocked() && $requisition->status === RequisitionStatus::RevisionSamSm) {
            return $this->spawnVersion($requisition, $user, 'Revisi GA setelah SAM/SM');
        }
        if ($version && $version->isLocked() && in_array($requisition->status, [RequisitionStatus::PendingGa, RequisitionStatus::RevisionGa], true)) {
            throw new BusinessRuleException('Versi final terkunci. Kembalikan pengajuan untuk revisi terlebih dahulu.');
        }

        return $version;
    }

    private function spawnVersion(Requisition $requisition, User $user, string $reason): RequisitionVersion
    {
        $old = $requisition->latestVersion();
        $nextNumber = $requisition->current_version + 1;
        $version = RequisitionVersion::query()->create([
            'requisition_id' => $requisition->id,
            'version_number' => $nextNumber,
            'created_by' => $user->id,
            'reason' => $reason,
            'estimated_total' => 0,
        ]);

        if ($old) {
            foreach ($old->items as $item) {
                RequisitionItem::query()->create([
                    'requisition_id' => $requisition->id,
                    'requisition_version_id' => $version->id,
                    'item_id' => $item->item_id,
                    'item_code_snapshot' => $item->item_code_snapshot,
                    'item_name_snapshot' => $item->item_name_snapshot,
                    'category_name_snapshot' => $item->category_name_snapshot,
                    'unit_name_snapshot' => $item->unit_name_snapshot,
                    'quantity_requested' => $item->quantity_requested,
                    'quantity_manager_approved' => $item->quantity_manager_approved,
                    'quantity_ga_recommended' => $item->quantity_ga_recommended,
                    'quantity_ga_final' => $item->quantity_ga_final,
                    'quantity_sam_approved' => null,
                    'quantity_sm_approved' => null,
                    'reference_unit_price' => $item->reference_unit_price,
                    'estimated_subtotal' => $item->estimated_subtotal,
                    'need_reason' => $item->need_reason,
                    'ga_change_reason' => $item->ga_change_reason,
                    'quantity_change_log' => $item->quantity_change_log,
                ]);
            }
            $version->update(['estimated_total' => $old->estimated_total]);
        }

        $requisition->update(['current_version' => $nextNumber]);

        return $version->fresh('items');
    }

    private function replaceVersionItems(Requisition $requisition, RequisitionVersion $version, array $items): void
    {
        $version->items()->delete();
        $total = 0;
        foreach ($items as $row) {
            $item = Item::query()->with(['category', 'unit'])->findOrFail($row['item_id']);
            $qty = (float) $row['quantity'];
            $subtotal = $qty * (float) $item->reference_price;
            $total += $subtotal;
            RequisitionItem::query()->create([
                'requisition_id' => $requisition->id,
                'requisition_version_id' => $version->id,
                'item_id' => $item->id,
                'item_code_snapshot' => $item->code,
                'item_name_snapshot' => $item->name,
                'category_name_snapshot' => $item->category->name,
                'unit_name_snapshot' => $item->unit->name,
                'quantity_requested' => $qty,
                'reference_unit_price' => $item->reference_price,
                'estimated_subtotal' => $subtotal,
                'need_reason' => $row['need_reason'],
            ]);
        }
        $version->update(['estimated_total' => $total]);
        $requisition->update(['estimated_total' => $total, 'current_version' => $version->version_number]);
    }

    private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw new BusinessRuleException('Minimal satu barang harus diisi.');
        }
        $ids = array_column($items, 'item_id');
        if (count($ids) !== count(array_unique($ids))) {
            throw new BusinessRuleException('Barang duplikat tidak diizinkan dalam satu pengajuan.');
        }
        foreach ($items as $item) {
            if ((float) ($item['quantity'] ?? 0) <= 0 || blank($item['need_reason'] ?? null)) {
                throw new BusinessRuleException('Kuantitas dan alasan kebutuhan wajib diisi.');
            }
        }

        return $items;
    }

    private function assertStaffCanWrite(User $user, ?Requisition $requisition): void
    {
        if (! $user->hasPermission('requisitions.create')) {
            throw new BusinessRuleException('Tidak berwenang membuat pengajuan.');
        }
        if (! $user->division_id) {
            throw new BusinessRuleException('Pengguna belum terhubung ke divisi.');
        }
        if ($requisition && $requisition->division_id !== $user->division_id) {
            throw new BusinessRuleException('Pengajuan di luar divisi Anda.');
        }
    }

    private function assertEditableByStaff(Requisition $requisition): void
    {
        if (! in_array($requisition->status, [
            RequisitionStatus::Draft,
            RequisitionStatus::RevisionDivision,
        ], true)) {
            throw new BusinessRuleException('Pengajuan tidak dapat diubah pada status saat ini.');
        }
    }

    private function ensureStep(Requisition $requisition, RequisitionVersion $version, string $step): void
    {
        ApprovalStep::query()->firstOrCreate(
            ['requisition_version_id' => $version->id, 'step' => $step],
            ['requisition_id' => $requisition->id, 'status' => 'pending']
        );
    }

    private function closeStep(Requisition $requisition, RequisitionVersion $version, string $step, User $actor, string $status, ?string $comment): void
    {
        ApprovalStep::query()->updateOrCreate(
            ['requisition_version_id' => $version->id, 'step' => $step],
            [
                'requisition_id' => $requisition->id,
                'status' => $status,
                'actor_id' => $actor->id,
                'comments' => $comment,
                'decided_at' => now(),
            ]
        );
    }

    private function recordHistory(
        User $user,
        Requisition $requisition,
        RequisitionVersion $version,
        string $role,
        string $step,
        ApprovalAction $action,
        ?string $comment,
    ): void {
        ApprovalHistory::query()->create([
            'requisition_id' => $requisition->id,
            'requisition_version_id' => $version->id,
            'actor_id' => $user->id,
            'role_slug' => $role,
            'step' => $step,
            'action' => $action->value,
            'comments' => $comment,
            'decided_at' => now(),
        ]);
    }
}
