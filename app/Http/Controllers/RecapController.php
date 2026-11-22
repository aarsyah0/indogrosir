<?php

namespace App\Http\Controllers;

use App\Exports\RecapExport;
use App\Enums\RequisitionStatus;
use App\Models\Division;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Supplier;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RecapController extends Controller
{
    public function __invoke(Request $request, ReportService $reports)
    {
        $filters = $request->only(['year', 'month', 'division_id', 'item_id', 'supplier_id', 'item_category_id', 'status']);
        $filters = $this->filtersWithPeriod($request, $filters);
        $rows = $reports->recap($request->user(), $filters);

        if ($request->string('export')->toString() === 'xlsx') {
            return Excel::download(new RecapExport($rows), 'rekap-pembelian.xlsx');
        }

        return view('recap.index', [
            'rows' => $rows,
            'divisions' => Division::query()
                ->when(! $request->user()->hasPermission('requisitions.view_all'), fn ($q) => $q->whereKey($request->user()->division_id ?? 0))
                ->orderBy('name')->get(),
            'items' => Item::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'categories' => ItemCategory::query()->orderBy('name')->get(),
            'statuses' => collect(RequisitionStatus::cases()),
            'filters' => $filters,
        ]);
    }

    public function pdf(Request $request, ReportService $reports)
    {
        $filters = $this->filtersWithPeriod($request, $request->only(['year', 'month', 'division_id', 'item_id', 'supplier_id', 'item_category_id', 'status']));
        $rows = $reports->recap($request->user(), $filters);
        $pdf = Pdf::loadView('recap.pdf', compact('rows', 'filters'))->setPaper('a4', 'landscape');

        return $pdf->download('rekap-pembelian.pdf');
    }

    private function filtersWithPeriod(Request $request, array $filters): array
    {
        if ($period = $request->string('period')->toString()) {
            [$filters['year'], $filters['month']] = array_map('intval', explode('-', $period));
        }

        return $filters;
    }
}
