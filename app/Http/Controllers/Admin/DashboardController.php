<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\AdminAuditLog;
use App\Models\EmployeeEligibility;
use App\Models\Schedule;
use App\Models\User;
use App\Models\ValidationEvent;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = EmployeeEligibility::where('status', 'active')->count();
        $registeredUsers = User::whereNotNull('employee_eligibility_id')->count();
        $todaysRides = ValidationEvent::whereDate('scanned_at', today())->count();
        $monthlyRides = ValidationEvent::whereMonth('scanned_at', now()->month)->whereYear('scanned_at', now()->year)->count();
        $pendingRequests = AccessRequest::where('status', 'pending')->count();

        $recentActivity = AdminAuditLog::with('actor')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $schedules = Schedule::where('active', true)->orderBy('starts_at')->get();

        $todayScheduleData = [];
        foreach ($schedules as $schedule) {
            $ridesCount = ValidationEvent::where('schedule_id', $schedule->id)
                ->whereDate('scanned_at', today())
                ->count();
            $todayScheduleData[] = [
                'schedule' => $schedule,
                'rides' => $ridesCount,
            ];
        }

        return view('admin.dashboard', [
            'totalEmployees' => $totalEmployees,
            'registeredUsers' => $registeredUsers,
            'todaysRides' => $todaysRides,
            'monthlyRides' => $monthlyRides,
            'pendingRequests' => $pendingRequests,
            'recentActivity' => $recentActivity,
            'todayScheduleData' => $todayScheduleData,
        ]);
    }
}
