<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\TrackingEvent;
use App\Models\ValidationEvent;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    public function index()
    {
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

        $trackingCounts = TrackingEvent::query()
            ->selectRaw('schedule_id, COUNT(*) as total')
            ->groupBy('schedule_id')
            ->pluck('total', 'schedule_id');

        $schedules = Schedule::query()->orderBy('starts_at')->get();

        return view('admin.reports.index', [
            'scheduleCounts' => $scheduleCounts,
            'busCounts' => $busCounts,
            'statusCounts' => $statusCounts,
            'trackingCounts' => $trackingCounts,
            'schedules' => $schedules,
        ]);
    }
}
