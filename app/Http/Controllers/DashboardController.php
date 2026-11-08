<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboards)
    {
        return view('dashboard', [
            'data' => $dashboards->for($request->user()),
        ]);
    }
}
