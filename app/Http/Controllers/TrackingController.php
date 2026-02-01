<?php

namespace App\Http\Controllers;

use App\Models\TrackingEvent;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function show(Request $request)
    {
        $data = $request->validate([
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'bus_id' => ['nullable', 'exists:buses,id'],
        ]);

        TrackingEvent::create([
            'schedule_id' => $data['schedule_id'] ?? null,
            'bus_id' => $data['bus_id'] ?? null,
            'user_id' => optional($request->user())->id,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accessed_at' => now(),
        ]);

        return view('tracking.show');
    }
}
