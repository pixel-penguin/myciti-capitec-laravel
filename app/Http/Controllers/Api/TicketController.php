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
        $ticketDate = now()->toDateString();

        $existing = TripTicket::query()
            ->where('user_id', $user->id)
            ->where('schedule_id', $schedule->id)
            ->where('ticket_date', $ticketDate)
            ->orderByDesc('id')
            ->first();

        if ($existing && $existing->status === 'active' && $existing->expires_at->isFuture()) {
            return response()->json([
                'ticket_id' => $existing->id,
                'qr_token' => $existing->qr_token,
                'expires_at' => $existing->expires_at,
            ]);
        }

        $endsTime = is_string($schedule->ends_at) ? $schedule->ends_at : $schedule->ends_at->format('H:i:s');
        $expiresAt = now()->setTimeFromTimeString($endsTime);

        $ticket = TripTicket::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'ticket_date' => $ticketDate,
            'qr_token' => (string) Str::uuid(),
            'status' => 'active',
            'issued_at' => now(),
            'expires_at' => $expiresAt->isPast() ? now()->addMinutes(30) : $expiresAt,
        ]);

        return response()->json([
            'ticket_id' => $ticket->id,
            'qr_token' => $ticket->qr_token,
            'expires_at' => $ticket->expires_at,
        ], 201);
    }
}
