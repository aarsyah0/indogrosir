<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        return view('master.suppliers.index', [
            'suppliers' => Supplier::query()
                ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                    ->where('code', 'like', '%'.$request->string('q')->toString().'%')
                    ->orWhere('name', 'like', '%'.$request->string('q')->toString().'%')
                    ->orWhere('contact_name', 'like', '%'.$request->string('q')->toString().'%')))
                ->orderBy('code')->paginate(20)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('master.suppliers.create');
    }

    public function edit(Supplier $supplier)
    {
        return view('master.suppliers.edit', compact('supplier'));
    }

    public function store(Request $request)
    {
        $supplier = Supplier::query()->create($this->validated($request) + ['is_active' => true]);
        $this->audit->log($request->user(), 'supplier.create', $supplier, null, $supplier->toArray());

        return redirect()->route('master.suppliers.index')->with('success', 'Pemasok ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $before = $supplier->toArray();
        $supplier->update($this->validated($request, $supplier->id) + ['is_active' => $request->boolean('is_active', true)]);
        $this->audit->log($request->user(), 'supplier.update', $supplier, $before, $supplier->fresh()->toArray());

        return redirect()->route('master.suppliers.index')->with('success', 'Pemasok diperbarui.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        if ($supplier->hasHistoricalTransactions()) {
            $supplier->update(['is_active' => false]);

            return back()->with('success', 'Pemasok dinonaktifkan karena memiliki histori.');
        }
        $supplier->delete();

        return back()->with('success', 'Pemasok dihapus.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:suppliers,code,'.($id ?? 'NULL')],
            'name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
