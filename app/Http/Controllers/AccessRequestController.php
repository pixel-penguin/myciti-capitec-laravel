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
