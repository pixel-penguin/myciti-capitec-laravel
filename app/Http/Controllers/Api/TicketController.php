<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\TripTicket;
use App\Models\ValidationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function issue(Request $request)
    {
        $data = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
        ]);

        $user = $request->user();

        if (! $user || $user->status !== 'active') {
            abort(403);
        }

        $schedule = Schedule::findOrFail($data['schedule_id']);
        $now = now();
        $ticketDate = $now->toDateString();
        $refreshSeconds = max(5, (int) config('ticketing.qr_refresh_seconds', 30));

        $alreadyScannedToday = ValidationEvent::query()
            ->whereHas('tripTicket', function ($q) use ($user, $schedule) {
                $q->where('user_id', $user->id)
                  ->where('schedule_id', $schedule->id);
            })
            ->where('event_type', 'pass')
            ->whereDate('scanned_at', $ticketDate)
            ->exists();

        if ($alreadyScannedToday) {
            return response()->json([
                'status' => 'already_scanned',
                'message' => 'Already scanned for this schedule today.',
            ], 422);
        }

        $existing = TripTicket::query()
            ->where('user_id', $user->id)
            ->where('schedule_id', $schedule->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        $expiresAt = $this->resolveExpiry($schedule, $now, $refreshSeconds);

        if ($existing) {
            $existing->update([
                'qr_token' => (string) Str::uuid(),
                'ticket_date' => $ticketDate,
                'issued_at' => $now,
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'ticket_id' => $existing->id,
                'qr_token' => $existing->qr_token,
                'expires_at' => $existing->expires_at,
                'schedule_name' => $schedule->name,
            ]);
        }

        $ticket = TripTicket::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'ticket_date' => $ticketDate,
            'qr_token' => (string) Str::uuid(),
            'status' => 'active',
            'issued_at' => $now,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'ticket_id' => $ticket->id,
            'qr_token' => $ticket->qr_token,
            'expires_at' => $ticket->expires_at,
            'schedule_name' => $schedule->name,
        ], 201);
    }

    private function resolveExpiry(Schedule $schedule, \Carbon\Carbon $now, int $refreshSeconds): \Carbon\Carbon
    {
        $endsTime = is_string($schedule->ends_at) ? $schedule->ends_at : $schedule->ends_at->format('H:i:s');
        $scheduleEnd = $now->copy()->setTimeFromTimeString($endsTime);
        $refreshExpiry = $now->copy()->addSeconds($refreshSeconds);

        if ($scheduleEnd->greaterThan($refreshExpiry)) {
            return $refreshExpiry;
        }

        if ($scheduleEnd->lessThanOrEqualTo($now)) {
            return $refreshExpiry;
        }

        return $scheduleEnd;
    }
}
