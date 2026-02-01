<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\TrackingEvent;
use App\Models\ValidationEvent;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function index()
    {
        $driver = DB::getDriverName();
        $dateExpr = $driver === 'sqlite' ? "date(scanned_at)" : "DATE(scanned_at)";
        $hourExpr = $driver === 'sqlite' ? "strftime('%H', scanned_at)" : "HOUR(scanned_at)";

        $scheduleCounts = ValidationEvent::query()
            ->selectRaw('schedule_id, COUNT(*) as total')
            ->groupBy('schedule_id')
            ->pluck('total', 'schedule_id');

        $busCounts = ValidationEvent::query()
            ->selectRaw('bus_id, COUNT(*) as total')
            ->groupBy('bus_id')
            ->pluck('total', 'bus_id');

        $statusCounts = ValidationEvent::query()
            ->selectRaw('event_type, COUNT(*) as total')
            ->groupBy('event_type')
            ->pluck('total', 'event_type');

        $dailyUsage = ValidationEvent::query()
            ->selectRaw("{$dateExpr} as day, COUNT(*) as total")
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->limit(14)
            ->get();

        $hourlyUsage = ValidationEvent::query()
            ->selectRaw("{$hourExpr} as hour, COUNT(*) as total")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $trackingCounts = TrackingEvent::query()
            ->selectRaw('schedule_id, COUNT(*) as total')
            ->groupBy('schedule_id')
            ->pluck('total', 'schedule_id');

        $schedules = Schedule::query()->orderBy('starts_at')->get();
        $totalEvents = $statusCounts->sum();
        $passRate = $totalEvents > 0 ? round((($statusCounts['pass'] ?? 0) / $totalEvents) * 100, 1) : 0;
        $declineRate = $totalEvents > 0 ? round((($statusCounts['decline'] ?? 0) / $totalEvents) * 100, 1) : 0;

        return view('admin.reports.index', [
            'scheduleCounts' => $scheduleCounts,
            'busCounts' => $busCounts,
            'statusCounts' => $statusCounts,
            'dailyUsage' => $dailyUsage,
            'hourlyUsage' => $hourlyUsage,
            'trackingCounts' => $trackingCounts,
            'schedules' => $schedules,
            'passRate' => $passRate,
            'declineRate' => $declineRate,
        ]);
    }
}
