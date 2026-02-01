<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\AdminAuditLog;
use App\Models\EmployeeEligibility;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function index()
    {
        $requests = AccessRequest::query()
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.access-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function approve(Request $request, AccessRequest $accessRequest)
    {
        if ($accessRequest->status !== 'pending') {
            return redirect()->back()->withErrors(['status' => 'Request already processed.']);
        }

        $employee = EmployeeEligibility::updateOrCreate(
            ['email' => $accessRequest->email],
            [
                'status' => 'active',
                'source' => 'access_request',
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]
        );

        $accessRequest->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'employee_eligibility_id' => $employee->id,
        ]);

        $this->audit($request, 'access_request.approve', $accessRequest->id, [
            'employee_eligibility_id' => $employee->id,
        ]);

        return redirect()->route('admin.access-requests.index')->with('status', 'Request approved.');
    }

    public function decline(Request $request, AccessRequest $accessRequest)
    {
        if ($accessRequest->status !== 'pending') {
            return redirect()->back()->withErrors(['status' => 'Request already processed.']);
        }

        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $accessRequest->update([
            'status' => 'declined',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $data['review_notes'] ?? null,
        ]);

        $this->audit($request, 'access_request.decline', $accessRequest->id);

        return redirect()->route('admin.access-requests.index')->with('status', 'Request declined.');
    }

    private function audit(Request $request, string $action, int $targetId, ?array $metadata = null): void
    {
        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'target_type' => AccessRequest::class,
            'target_id' => $targetId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
