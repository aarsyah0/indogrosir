<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalAction;
use App\Enums\RequisitionStatus;
use App\Http\Requests\ApprovalDecisionRequest;
use App\Http\Requests\GaVerifyRequest;
use App\Models\Requisition;
use App\Services\RequisitionWorkflowService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(private RequisitionWorkflowService $workflow) {}

    public function managerIndex(Request $request)
    {
        $ids = $request->user()->managedDivisions()->pluck('divisions.id');
        $requisitions = Requisition::query()
            ->with(['division', 'requester', 'period'])
            ->whereIn('division_id', $ids)
            ->where('status', RequisitionStatus::PendingManager)
            ->latest()
            ->paginate(15);

        return view('approvals.index', [
            'title' => 'Approval Manager',
            'requisitions' => $requisitions,
            'showRoute' => 'approvals.manager.show',
        ]);
    }

    public function managerShow(Requisition $requisition)
    {
        $this->authorize('approveManager', $requisition);

        return $this->show($requisition, 'approvals.manager.decide', true);
    }

    public function managerDecide(ApprovalDecisionRequest $request, Requisition $requisition)
    {
        $this->authorize('approveManager', $requisition);
        $this->workflow->managerDecide(
            $request->user(),
            $requisition,
            ApprovalAction::from($request->validated('action')),
            $request->validated('comment')
        );

        return redirect()->route('approvals.manager.index')->with('success', 'Keputusan Manager tersimpan.');
    }

    public function gaIndex()
    {
        $requisitions = Requisition::query()
            ->with(['division', 'requester', 'period'])
            ->whereIn('status', [RequisitionStatus::PendingGa, RequisitionStatus::RevisionGa, RequisitionStatus::RevisionSamSm])
            ->latest()
            ->paginate(15);

        return view('approvals.index', [
            'title' => 'Verifikasi GA',
            'requisitions' => $requisitions,
            'showRoute' => 'approvals.ga.show',
        ]);
    }

    public function gaShow(Requisition $requisition)
    {
        $this->authorize('verifyGa', $requisition);
        $rows = $this->workflow->verificationContext($requisition);
        $requisition->load(['division', 'period', 'requester', 'approvalHistories.actor']);

        return view('approvals.ga', compact('requisition', 'rows'));
    }

    public function gaDecide(GaVerifyRequest $request, Requisition $requisition)
    {
        $this->authorize('verifyGa', $requisition);
        $intent = $request->validated('intent');
        $this->workflow->gaUpdate(
            $request->user(),
            $requisition,
            $request->validated('lines'),
            $request->validated('return_comment'),
            $intent === 'submit_sam',
            $intent === 'return_division',
        );

        return redirect()->route('approvals.ga.index')->with('success', 'Verifikasi GA disimpan.');
    }

    public function samIndex()
    {
        return $this->queue(RequisitionStatus::PendingSam, 'Approval SAM', 'approvals.sam.show');
    }

    public function samShow(Requisition $requisition)
    {
        $this->authorize('approveSam', $requisition);

        return $this->show($requisition, 'approvals.sam.decide', false);
    }

    public function samDecide(ApprovalDecisionRequest $request, Requisition $requisition)
    {
        $this->authorize('approveSam', $requisition);
        $this->workflow->samDecide(
            $request->user(),
            $requisition,
            ApprovalAction::from($request->validated('action')),
            $request->validated('comment')
        );

        return redirect()->route('approvals.sam.index')->with('success', 'Keputusan SAM tersimpan.');
    }

    public function smIndex()
    {
        return $this->queue(RequisitionStatus::PendingSm, 'Approval SM', 'approvals.sm.show');
    }

    public function smShow(Requisition $requisition)
    {
        $this->authorize('approveSm', $requisition);

        return $this->show($requisition, 'approvals.sm.decide', false);
    }

    public function smDecide(ApprovalDecisionRequest $request, Requisition $requisition)
    {
        $this->authorize('approveSm', $requisition);
        $this->workflow->smDecide(
            $request->user(),
            $requisition,
            ApprovalAction::from($request->validated('action')),
            $request->validated('comment')
        );

        return redirect()->route('approvals.sm.index')->with('success', 'Keputusan SM tersimpan.');
    }

    private function queue(RequisitionStatus $status, string $title, string $showRoute)
    {
        $requisitions = Requisition::query()
            ->with(['division', 'requester', 'period'])
            ->where('status', $status)
            ->latest()
            ->paginate(15);

        return view('approvals.index', compact('title', 'requisitions', 'showRoute'));
    }

    private function show(Requisition $requisition, string $actionRoute, bool $allowReject)
    {
        $requisition->load(['division', 'period', 'requester', 'approvalHistories.actor']);
        $version = $requisition->latestVersion()?->load('items');

        return view('approvals.show', compact('requisition', 'version', 'actionRoute', 'allowReject'));
    }
}
