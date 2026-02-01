<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceMessage;

class ServiceMessageController extends Controller
{
    public function current()
    {
        $now = now();

        $message = ServiceMessage::query()
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->first();

        if (! $message) {
            return response()->json(['message' => null]);
        }

        return response()->json([
            'message' => [
                'id' => $message->id,
                'title' => $message->title,
                'body' => $message->body,
                'level' => $message->level,
                'starts_at' => $message->starts_at,
                'ends_at' => $message->ends_at,
                'updated_at' => $message->updated_at,
            ],
        ]);
    }
}
