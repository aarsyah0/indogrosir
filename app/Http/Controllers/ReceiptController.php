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
            ->with(['supplier', 'requisition.division'])
            ->latest('purchased_at')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('procurement_order_items as poi')
                    ->whereColumn('poi.procurement_order_id', 'procurement_orders.id')
                    ->whereRaw('poi.quantity_purchased > COALESCE((SELECT SUM(gri.quantity_received) FROM goods_receipts gr INNER JOIN goods_receipt_items gri ON gri.goods_receipt_id = gr.id WHERE gr.procurement_order_id = procurement_orders.id AND gri.item_id = poi.item_id), 0) + 0.0001');
            })
            ->paginate(10, ['*'], 'pending_page');

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
