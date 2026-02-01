<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripTicket;
use App\Models\ValidationEvent;
use Illuminate\Http\Request;

class ValidationEventController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'qr_token' => ['required', 'string'],
            'event_type' => ['required', 'in:pass,decline,alight'],
            'bus_id' => ['nullable', 'exists:buses,id'],
            'validator_id' => ['nullable', 'string'],
            'scanned_at' => ['nullable', 'date'],
        ]);

        $ticket = TripTicket::where('qr_token', $data['qr_token'])->first();
        $scannedAt = isset($data['scanned_at']) ? now()->parse($data['scanned_at']) : now();

        if (! $ticket) {
            ValidationEvent::create([
                'event_type' => 'decline',
                'scanned_at' => $scannedAt,
                'validator_id' => $data['validator_id'] ?? null,
                'bus_id' => $data['bus_id'] ?? null,
                'metadata' => ['reason' => 'ticket_not_found'],
            ]);

            return response()->json(['status' => 'declined', 'reason' => 'ticket_not_found'], 422);
        }

        $eventType = $data['event_type'];
        $declineReason = null;

        if ($ticket->status !== 'active') {
            $eventType = 'decline';
            $declineReason = 'already_used';
        } elseif ($ticket->expires_at->isPast()) {
            $eventType = 'decline';
            $declineReason = 'expired';
        }

        $event = ValidationEvent::create([
            'user_id' => $ticket->user_id,
            'trip_ticket_id' => $ticket->id,
            'schedule_id' => $ticket->schedule_id,
            'bus_id' => $data['bus_id'] ?? null,
            'event_type' => $eventType,
            'scanned_at' => $scannedAt,
            'validator_id' => $data['validator_id'] ?? null,
            'metadata' => $declineReason ? ['reason' => $declineReason] : null,
        ]);

        if ($eventType === 'pass') {
            $ticket->update(['status' => 'used']);
        }

        return response()->json([
            'status' => $eventType === 'decline' ? 'declined' : 'ok',
            'event_id' => $event->id,
        ]);
    }
}
