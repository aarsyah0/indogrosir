<?php

namespace App\Http\Controllers;

use App\Enums\RequisitionStatus;
use App\Http\Requests\StoreProcurementRequest;
use App\Models\ProcurementOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Services\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcurementController extends Controller
{
    public function __construct(private ProcurementService $procurement) {}

    public function index(Request $request)
    {
        $orders = ProcurementOrder::query()
            ->with(['requisition.division', 'supplier'])
            ->latest()
            ->paginate(15);

        return view('procurements.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $requisition = Requisition::query()
            ->where('status', RequisitionStatus::ApprovedForPurchase)
            ->findOrFail($request->integer('requisition_id'));
        $version = $requisition->latestVersion()?->load('items');
        $suppliers = Supplier::query()->where('is_active', true)->orderBy('name')->get();

        return view('procurements.create', compact('requisition', 'version', 'suppliers'));
    }

    public function store(StoreProcurementRequest $request)
    {
        $requisition = Requisition::query()->findOrFail($request->validated('requisition_id'));
        $this->authorize('procure', $requisition);
        $order = $this->procurement->create(
            $request->user(),
            $requisition,
            $request->validated(),
            $request->file('proof')
        );

        return redirect()->route('procurements.show', $order)->with('success', 'Pembelian dicatat.');
    }

    public function show(ProcurementOrder $procurement)
    {
        $procurement->load(['requisition.division', 'supplier', 'items', 'attachments', 'receipts.items']);
        $receivedByItem = $procurement->receipts
            ->flatMap->items
            ->groupBy('item_id')
            ->map(fn ($lines) => (float) $lines->sum('quantity_received'));
        $remainingByItem = $procurement->items->mapWithKeys(fn ($line) => [
            $line->item_id => max((float) $line->quantity_purchased - (float) ($receivedByItem[$line->item_id] ?? 0), 0),
        ]);
        $hasRemaining = $remainingByItem->contains(fn ($quantity) => $quantity > 0.0001);

        return view('procurements.show', [
            'order' => $procurement,
            'remainingByItem' => $remainingByItem,
            'hasRemaining' => $hasRemaining,
        ]);
    }

    public function attachment(ProcurementOrder $procurement, int $attachment)
    {
        $file = $procurement->attachments()->findOrFail($attachment);

        return Storage::download($file->path, $file->original_name);
    }
}
