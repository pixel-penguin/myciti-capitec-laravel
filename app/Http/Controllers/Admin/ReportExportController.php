<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\TrackingEvent;
use App\Models\ValidationEvent;
use Illuminate\Http\Request;

class ReportExportController extends Controller
{
    public function export(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:validation,tracking'],
        ]);

        $filename = $data['type'].'-report-'.now()->format('Ymd_His').'.csv';

        $this->audit($request, 'report.export', null, ['type' => $data['type']]);

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');

            if ($data['type'] === 'validation') {
                fputcsv($output, ['event_type', 'schedule_id', 'bus_id', 'count']);
                ValidationEvent::query()
                    ->selectRaw('event_type, schedule_id, bus_id, COUNT(*) as total')
                    ->groupBy('event_type', 'schedule_id', 'bus_id')
                    ->orderBy('event_type')
                    ->chunk(500, function ($rows) use ($output) {
                        foreach ($rows as $row) {
                            fputcsv($output, [
                                $row->event_type,
                                $row->schedule_id,
                                $row->bus_id,
                                $row->total,
                            ]);
                        }
                    });
            } else {
                fputcsv($output, ['schedule_id', 'bus_id', 'count']);
                TrackingEvent::query()
                    ->selectRaw('schedule_id, bus_id, COUNT(*) as total')
                    ->groupBy('schedule_id', 'bus_id')
                    ->orderBy('schedule_id')
                    ->chunk(500, function ($rows) use ($output) {
                        foreach ($rows as $row) {
                            fputcsv($output, [
                                $row->schedule_id,
                                $row->bus_id,
                                $row->total,
                            ]);
                        }
                    });
            }

            fclose($output);
        }, $filename);
    }

    private function audit(Request $request, string $action, ?int $targetId, ?array $metadata = null): void
    {
        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'target_type' => 'report',
            'target_id' => $targetId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
