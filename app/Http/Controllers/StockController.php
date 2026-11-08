<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Models\Division;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::query()->with(['category', 'unit'])->orderBy('name')->get()->map(function (Item $item) {
            $txs = $item->stockTransactions;
            $in = (float) $txs->filter(fn ($t) => in_array($t->type, [StockMovementType::In, StockMovementType::Opening], true))->sum('quantity');
            $out = (float) $txs->filter(fn ($t) => $t->type === StockMovementType::Out)->sum('quantity');
            $adj = (float) $txs->filter(fn ($t) => $t->type === StockMovementType::Adjustment)->sum('quantity');
            $end = $item->currentStock();

            return [
                'item' => $item,
                'opening' => $end - $in + $out - $adj,
                'in' => $in,
                'out' => $out,
                'adj' => $adj,
                'end' => $end,
                'low' => $end <= (float) $item->min_stock,
            ];
        });

        $ledger = StockTransaction::query()->with(['item', 'creator'])->latest()->paginate(20);
        $divisions = Division::query()->where('is_active', true)->orderBy('name')->get();

        return view('stock.index', compact('items', 'ledger', 'divisions'));
    }

    public function issue(Request $request, StockService $stock)
    {
        abort_unless($request->user()->hasPermission('stock.manage'), 403);

        $data = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['required', 'string', 'max:1000'],
            'division_id' => ['nullable', 'exists:divisions,id'],
        ]);

        $stock->move(
            $request->user(),
            Item::query()->findOrFail($data['item_id']),
            StockMovementType::Out,
            (float) $data['quantity'],
            null,
            $data['notes'],
            $data['division_id'] ?? $request->user()->division_id,
        );

        return back()->with('success', 'Pengeluaran stok dicatat.');
    }

    public function adjust(Request $request, StockService $stock)
    {
        abort_unless($request->user()->hasPermission('stock.manage'), 403);
        $data = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'quantity_delta' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $stock->adjust($request->user(), Item::query()->findOrFail($data['item_id']), (float) $data['quantity_delta'], $data['reason']);

        return back()->with('success', 'Penyesuaian stok dicatat.');
    }
}
