<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public function __invoke(Request $request, ReportService $reports)
    {
        $filters = $request->only(['year', 'division_id', 'item_category_id', 'item_id']);
        $charts = $reports->charts($filters, $request->user());

        return view('charts.index', [
            'charts' => $charts,
            'filters' => $filters,
            'divisions' => Division::query()
                ->when(! $request->user()->hasPermission('requisitions.view_all'), fn ($q) => $q->whereKey($request->user()->division_id ?? 0))
                ->orderBy('name')->get(),
            'categories' => ItemCategory::query()->orderBy('name')->get(),
            'items' => Item::query()->orderBy('name')->get(),
        ]);
    }
}
