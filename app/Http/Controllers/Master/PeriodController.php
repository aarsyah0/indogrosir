<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\RequestPeriod;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PeriodController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        return view('master.periods.index', [
            'periods' => RequestPeriod::query()->with('opener')
                ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                    ->where('name', 'like', '%'.$request->string('q')->toString().'%')
                    ->when(is_numeric($request->string('q')->toString()), fn ($yearSearch) => $yearSearch
                        ->orWhere('year', (int) $request->string('q')->toString()))))
                ->orderByDesc('year')->orderByDesc('month')->paginate(20)->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'start_date' => ['required', 'date'],
            'deadline_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        $name = Carbon::create((int) $data['year'], (int) $data['month'], 1)->translatedFormat('F Y');
        $period = RequestPeriod::query()->create($data + [
            'name' => $name,
            'status' => 'open',
            'opened_by' => $request->user()->id,
        ]);
        $this->audit->log($request->user(), 'period.open', $period, null, $period->toArray());

        return back()->with('success', 'Periode pengajuan dibuka.');
    }

    public function update(Request $request, RequestPeriod $period)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'deadline_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:open,closed'],
        ]);
        $before = $period->toArray();
        $period->update($data);
        $this->audit->log($request->user(), 'period.update', $period, $before, $period->fresh()->toArray());

        return back()->with('success', 'Periode diperbarui.');
    }
}
