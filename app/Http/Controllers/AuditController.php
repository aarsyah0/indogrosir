<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = AuditLog::query()->with('actor')->latest('created_at');
        if ($q = $request->string('q')->toString()) {
            $query->where('action', 'like', "%$q%");
        }

        return view('audit.index', [
            'logs' => $query->paginate(30)->withQueryString(),
        ]);
    }
}
