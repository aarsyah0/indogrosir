<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\DivisionManager;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        return view('master.divisions.index', [
            'divisions' => Division::query()->with('activeManagers')
                ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                    ->where('code', 'like', '%'.$request->string('q')->toString().'%')
                    ->orWhere('name', 'like', '%'.$request->string('q')->toString().'%')))
                ->orderBy('code')->paginate(20)->withQueryString(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:divisions,code'],
            'name' => ['required', 'string', 'max:150'],
        ]);
        $division = Division::query()->create($data + ['is_active' => true]);
        $this->audit->log($request->user(), 'division.create', $division, null, $division->toArray());

        return back()->with('success', 'Divisi ditambahkan.');
    }

    public function update(Request $request, Division $division)
    {
        $before = $division->toArray();
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:divisions,code,'.$division->id],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ]);
        $division->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active', $division->is_active),
        ]);
        if (! empty($data['manager_id'])) {
            DivisionManager::query()
                ->where('division_id', $division->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);
            DivisionManager::query()->create([
                'division_id' => $division->id,
                'user_id' => $data['manager_id'],
                'started_at' => now(),
            ]);
        }
        $this->audit->log($request->user(), 'division.update', $division, $before, $division->fresh()->toArray());

        return back()->with('success', 'Divisi diperbarui.');
    }

    public function users()
    {
        return User::query()->orderBy('name')->get(['id', 'name', 'email']);
    }
}
