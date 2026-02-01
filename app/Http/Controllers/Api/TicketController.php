<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\TripTicket;
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

        $alreadyUsed = TripTicket::query()
            ->where('user_id', $user->id)
            ->where('schedule_id', $schedule->id)
            ->where('ticket_date', $ticketDate)
            ->where('status', 'used')
            ->exists();

        if ($alreadyUsed) {
            return response()->json([
                'status' => 'already_used',
                'message' => 'Ticket already used for this schedule.',
            ], 422);
        }

        $existing = TripTicket::query()
            ->where('user_id', $user->id)
            ->where('schedule_id', $schedule->id)
            ->where('ticket_date', $ticketDate)
            ->orderByDesc('id')
            ->first();

        $expiresAt = $this->resolveExpiry($schedule, $now, $refreshSeconds);

        if ($existing && $existing->status === 'active') {
            $existing->update([
                'qr_token' => (string) Str::uuid(),
                'issued_at' => $now,
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'ticket_id' => $existing->id,
                'qr_token' => $existing->qr_token,
                'expires_at' => $existing->expires_at,
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
