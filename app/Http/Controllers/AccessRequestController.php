<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\EmployeeEligibility;
use Illuminate\Http\Request;

class AccessRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $alreadyEligible = EmployeeEligibility::where('email', $data['email'])->exists();
        if ($alreadyEligible) {
            return response()->json([
                'status' => 'already_eligible',
                'message' => 'Email is already eligible. Please register.',
            ], 200);
        }

        $existing = AccessRequest::query()
            ->where('email', $data['email'])
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Access request already submitted.',
            ], 200);
        }

        $requestRow = AccessRequest::create([
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
            'department' => $data['department'] ?? null,
            'status' => 'pending',
            'reason' => $data['reason'] ?? null,
            'requested_at' => now(),
        ]);

        return response()->json([
            'status' => 'submitted',
            'request_id' => $requestRow->id,
        ], 201);
    }
}
