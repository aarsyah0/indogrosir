<?php

namespace App\Http\Controllers;

use App\Enums\RequisitionStatus;
use App\Http\Requests\StoreRequisitionRequest;
use App\Models\Item;
use App\Models\RequestPeriod;
use App\Models\Requisition;
use App\Services\RequisitionWorkflowService;
use Illuminate\Http\Request;

class RequisitionController extends Controller
{
    public function __construct(private RequisitionWorkflowService $workflow) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Requisition::query()->with(['division', 'period', 'requester'])->latest();

        if (! $user->hasPermission('requisitions.view_all')) {
            $query->where('division_id', $user->division_id);
        }

        if ($search = $request->string('q')->toString()) {
            $query->where('number', 'like', '%'.$search.'%');
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return view('requisitions.index', [
            'requisitions' => $query->paginate(15)->withQueryString(),
            'statuses' => collect(RequisitionStatus::cases()),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Requisition::class);

        return view('requisitions.form', $this->formData());
    }

    public function store(StoreRequisitionRequest $request)
    {
        $requisition = $this->workflow->save($request->user(), $request->validated());

        if ($request->boolean('submit')) {
            $requisition = $this->workflow->submit($request->user(), $requisition);
            $message = 'Pengajuan dikirim kepada Manager.';
        } else {
            $message = 'Draft pengajuan disimpan.';
        }

        return redirect()->route('requisitions.show', $requisition)->with('success', $message);
    }

    public function show(Requisition $requisition)
    {
        $this->authorize('view', $requisition);
        $requisition->load(['division', 'period', 'requester', 'approvalHistories.actor', 'procurementOrders.items']);
        $version = $requisition->latestVersion()?->load('items.item');

        return view('requisitions.show', compact('requisition', 'version'));
    }

    public function edit(Requisition $requisition)
    {
        $this->authorize('update', $requisition);

        return view('requisitions.form', array_merge($this->formData(), [
            'requisition' => $requisition,
            'version' => $requisition->latestVersion()?->load('items'),
        ]));
    }

    public function update(StoreRequisitionRequest $request, Requisition $requisition)
    {
        $this->authorize('update', $requisition);
        $requisition = $this->workflow->save($request->user(), $request->validated(), $requisition);

        if ($request->boolean('submit')) {
            $requisition = $this->workflow->submit($request->user(), $requisition);
            $message = 'Pengajuan dikirim ulang kepada Manager.';
        } else {
            $message = 'Pengajuan diperbarui.';
        }

        return redirect()->route('requisitions.show', $requisition)->with('success', $message);
    }

    public function preview(Request $request, Requisition $requisition)
    {
        $this->authorize('view', $requisition);
        $version = $requisition->latestVersion()?->load('items');
        $autoPrint = $request->boolean('print');

        return view('requisitions.preview', compact('requisition', 'version', 'autoPrint'));
    }

    private function formData(): array
    {
        return [
            'periods' => RequestPeriod::query()->where('status', 'open')->orderByDesc('year')->orderByDesc('month')->get(),
            'items' => Item::query()->where('is_active', true)->with(['category', 'unit'])->orderBy('name')->get(),
            'requisition' => null,
            'version' => null,
        ];
    }
}
