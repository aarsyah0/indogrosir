<?php

namespace App\Http\Controllers;

use App\Enums\RequisitionStatus;
use App\Exports\MonthlyReportExport;
use App\Models\Division;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Supplier;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        $filters = $request->only(['year', 'month', 'division_id', 'item_category_id', 'item_id', 'status', 'supplier_id']);
        $report = $reports->monthly($request->user(), $filters);

        return view('reports.index', [
            'report' => $report,
            'divisions' => Division::query()
                ->when(! $request->user()->hasPermission('requisitions.view_all'), fn ($q) => $q->whereKey($request->user()->division_id ?? 0))
                ->orderBy('name')->get(),
            'categories' => ItemCategory::query()->orderBy('name')->get(),
            'items' => Item::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'statuses' => collect(RequisitionStatus::cases()),
        ]);
    }

    public function excel(Request $request, ReportService $reports)
    {
        $report = $reports->monthly($request->user(), $request->all());

        return Excel::download(new MonthlyReportExport($report), 'laporan-bulanan.xlsx');
    }

    public function pdf(Request $request, ReportService $reports)
    {
        $report = $reports->monthly($request->user(), $request->all());
        $pdf = Pdf::loadView('reports.pdf', compact('report'))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-bulanan.pdf');
    }
}
