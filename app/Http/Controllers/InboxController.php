<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Requisition::query()
            ->with(['division', 'period', 'requester'])
            ->where('status', '!=', 'draft')
            ->latest();

        if ($search = $request->string('q')->toString()) {
            $query->where('number', 'like', '%'.$search.'%');
        }

        return view('inbox.index', [
            'requisitions' => $query->paginate(20)->withQueryString(),
        ]);
    }
}
