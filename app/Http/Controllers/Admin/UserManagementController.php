<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->role(Role::all())
            ->with('roles')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $availableRoles = Role::query()->pluck('name')->all();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', 'in:active,suspended'],
            'two_factor_enabled' => ['sometimes', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in($availableRoles)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $roles = array_values(array_intersect($data['roles'] ?? [], $availableRoles));
        if (! empty($roles)) {
            $user->syncRoles($roles);
        }

        $this->audit($request, 'user.create', $user->id, [
            'roles' => $roles,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $availableRoles = Role::query()->pluck('name')->all();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', 'in:active,suspended'],
            'two_factor_enabled' => ['sometimes', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in($availableRoles)],
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);

        $roles = array_values(array_intersect($data['roles'] ?? [], $availableRoles));
        $user->syncRoles($roles);

        $this->audit($request, 'user.update', $user->id, [
            'roles' => $roles,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        $this->audit($request, 'user.delete', $user->id);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted.');
    }

    public function updateStatus(Request $request, User $user)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,suspended'],
        ]);

        $user->update([
            'status' => $data['status'],
        ]);

        $this->audit($request, 'user.status_update', $user->id, [
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User status updated.');
    }

    private function audit(Request $request, string $action, int $targetId, ?array $metadata = null): void
    {
        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'target_type' => User::class,
            'target_id' => $targetId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
