<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\ProcurementOrder;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(private ReceiptService $receipts) {}

    public function index()
    {
        $receipts = GoodsReceipt::query()->with(['order.supplier', 'order.requisition'])->latest()->paginate(15);
        $pendingOrders = ProcurementOrder::query()
            ->with(['supplier', 'requisition.division', 'items', 'receipts.items'])
            ->latest('purchased_at')
            ->get()
            ->filter(function (ProcurementOrder $order) {
                $receivedByItem = $order->receipts
                    ->flatMap->items
                    ->groupBy('item_id')
                    ->map(fn ($lines) => (float) $lines->sum('quantity_received'));

                return $order->items->contains(fn ($item) =>
                    (float) $item->quantity_purchased - (float) ($receivedByItem[$item->item_id] ?? 0) > 0.0001
                );
            })
            ->values();

        return view('receipts.index', compact('receipts', 'pendingOrders'));
    }

    public function create(Request $request)
    {
        $order = ProcurementOrder::query()
            ->with(['items', 'receipts.items'])
            ->findOrFail($request->integer('procurement_id'));
        $receivedByItem = $order->receipts
            ->flatMap->items
            ->groupBy('item_id')
            ->map(fn ($lines) => (float) $lines->sum('quantity_received'));
        $remainingByItem = $order->items->mapWithKeys(fn ($line) => [
            $line->item_id => max((float) $line->quantity_purchased - (float) ($receivedByItem[$line->item_id] ?? 0), 0),
        ]);

        return view('receipts.create', compact('order', 'remainingByItem'));
    }

    public function store(StoreReceiptRequest $request)
    {
        $order = ProcurementOrder::query()->with('items')->findOrFail($request->integer('procurement_id'));
        $receipt = $this->receipts->receive($request->user(), $order, $request->validated(), $request->file('proof'));

        return redirect()->route('receipts.show', $receipt)->with('success', 'Penerimaan dicatat. Stok diperbarui sesuai kuantitas diterima.');
    }

    public function show(GoodsReceipt $receipt)
    {
        $receipt->load(['order.supplier', 'order.requisition', 'items.item', 'attachments']);

        return view('receipts.show', compact('receipt'));
    }
}
