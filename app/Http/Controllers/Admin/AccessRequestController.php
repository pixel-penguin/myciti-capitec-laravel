<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\AdminAuditLog;
use App\Models\EmployeeEligibility;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'pending');

        $query = AccessRequest::query()->orderByDesc('id');

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        $requests = $query->paginate(25)->withQueryString();

        $pendingCount = AccessRequest::where('status', 'pending')->count();
        $approvedCount = AccessRequest::where('status', 'approved')->count();
        $declinedCount = AccessRequest::where('status', 'declined')->count();
        $allCount = AccessRequest::count();

        return view('admin.access-requests.index', [
            'requests' => $requests,
            'tab' => $tab,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'declinedCount' => $declinedCount,
            'allCount' => $allCount,
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
                'first_name' => $accessRequest->name ? explode(' ', $accessRequest->name)[0] : null,
                'last_name' => $accessRequest->name ? (explode(' ', $accessRequest->name, 2)[1] ?? null) : null,
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
