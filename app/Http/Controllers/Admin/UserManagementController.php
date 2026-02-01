<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with('roles')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.users.index', [
            'users' => $users,
        ]);
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
