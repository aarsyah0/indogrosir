<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        return view('users.index', [
            'users' => User::query()->with(['roles', 'division'])
                ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                    ->where('name', 'like', '%'.$request->string('q')->toString().'%')
                    ->orWhere('email', 'like', '%'.$request->string('q')->toString().'%')))
                ->orderBy('name')->paginate(20)->withQueryString(),
            'roles' => Role::query()->orderBy('name')->get(),
            'divisions' => Division::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
        ]);
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'division_id' => $data['division_id'] ?? null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->roles()->sync($data['roles']);
        $this->audit->log($request->user(), 'user.create', $user, null, $user->toArray());

        return back()->with('success', 'Pengguna ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $before = $user->toArray();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'division_id' => $data['division_id'] ?? null,
            'is_active' => $request->boolean('is_active', $user->is_active),
        ]);
        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }
        $user->roles()->sync($data['roles']);
        $this->audit->log($request->user(), 'user.update', $user, $before, $user->fresh()->toArray());

        return back()->with('success', 'Pengguna diperbarui.');
    }
}
