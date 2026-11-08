<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Services\ReportService;
use Illuminate\Http\Request;

class NeedsController extends Controller
{
    public function __invoke(Request $request, ReportService $reports)
    {
        $filters = $request->only(['year', 'month', 'division_id']);
        $report = $reports->monthly($request->user(), $filters);

        return view('needs.index', [
            'report' => $report,
            'divisions' => Division::query()
                ->when(! $request->user()->hasPermission('requisitions.view_all'), fn ($q) => $q->whereKey($request->user()->division_id ?? 0))
                ->orderBy('name')->get(),
        ]);
    }
}
