<?php

namespace App\Policies;

use App\Enums\RequisitionStatus;
use App\Models\Requisition;
use App\Models\User;

class RequisitionPolicy
{
    public function view(User $user, Requisition $requisition): bool
    {
        if ($user->hasPermission('requisitions.view_all')) {
            return true;
        }
        if ($user->hasPermission('approvals.manager') && $user->managesDivision($requisition->division_id)) {
            return true;
        }

        return $user->hasPermission('requisitions.view_own_division')
            && $user->division_id === $requisition->division_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('requisitions.create') && (bool) $user->division_id;
    }

    public function update(User $user, Requisition $requisition): bool
    {
        return $this->create($user)
            && $requisition->division_id === $user->division_id
            && in_array($requisition->status, [RequisitionStatus::Draft, RequisitionStatus::RevisionDivision], true);
    }

    public function approveManager(User $user, Requisition $requisition): bool
    {
        return $user->hasPermission('approvals.manager')
            && $user->managesDivision($requisition->division_id)
            && $user->id !== $requisition->requester_id;
    }

    public function verifyGa(User $user, Requisition $requisition): bool
    {
        return $user->hasPermission('approvals.ga');
    }

    public function approveSam(User $user, Requisition $requisition): bool
    {
        return $user->hasPermission('approvals.sam');
    }

    public function approveSm(User $user, Requisition $requisition): bool
    {
        return $user->hasPermission('approvals.sm');
    }

    public function procure(User $user, Requisition $requisition): bool
    {
        return $user->hasPermission('procurement.manage')
            && $requisition->status === RequisitionStatus::ApprovedForPurchase;
    }
}
