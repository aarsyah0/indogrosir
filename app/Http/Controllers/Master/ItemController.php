<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemPriceHistory;
use App\Models\Unit;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        $query = Item::query()->with(['category', 'unit']);
        if ($q = $request->string('q')->toString()) {
            $query->where(fn ($qq) => $qq->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%"));
        }

        return view('master.items.index', [
            'items' => $query->orderBy('code')->paginate(20)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('master.items.form', [
            'item' => new Item(['is_active' => true]),
            'categories' => ItemCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = Item::query()->create($data);
        ItemPriceHistory::query()->create([
            'item_id' => $item->id,
            'price' => $item->reference_price,
            'source' => 'master',
            'recorded_by' => $request->user()->id,
            'recorded_at' => now(),
        ]);
        $this->audit->log($request->user(), 'item.create', $item, null, $item->toArray());

        return redirect()->route('master.items.index')->with('success', 'Barang ditambahkan.');
    }

    public function edit(Item $item)
    {
        return view('master.items.form', [
            'item' => $item,
            'categories' => ItemCategory::query()->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $before = $item->toArray();
        $data = $this->validated($request, $item->id);
        if ((float) $data['reference_price'] !== (float) $item->reference_price) {
            ItemPriceHistory::query()->create([
                'item_id' => $item->id,
                'price' => $data['reference_price'],
                'source' => 'master',
                'recorded_by' => $request->user()->id,
                'notes' => 'Perubahan harga referensi',
                'recorded_at' => now(),
            ]);
        }
        $item->update($data);
        $this->audit->log($request->user(), 'item.update', $item, $before, $item->fresh()->toArray());

        return redirect()->route('master.items.index')->with('success', 'Barang diperbarui.');
    }

    public function destroy(Request $request, Item $item)
    {
        if ($item->hasHistoricalTransactions()) {
            $item->update(['is_active' => false]);
            $this->audit->log($request->user(), 'item.deactivate', $item, ['is_active' => true], ['is_active' => false], 'Memiliki transaksi historis');

            return back()->with('success', 'Barang dinonaktifkan karena memiliki histori transaksi.');
        }
        $item->delete();

        return back()->with('success', 'Barang dihapus.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:items,code,'.($id ?? 'NULL')],
            'name' => ['required', 'string', 'max:150'],
            'item_category_id' => ['required', 'exists:item_categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'description' => ['nullable', 'string'],
            'reference_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
