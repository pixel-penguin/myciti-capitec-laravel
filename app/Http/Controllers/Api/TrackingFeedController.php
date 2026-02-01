<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusLocation;
use Illuminate\Http\Request;

class TrackingFeedController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_code' => ['required', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'heading' => ['nullable', 'numeric'],
            'speed' => ['nullable', 'numeric'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $bus = Bus::where('code', $data['bus_code'])->first();
        if (! $bus) {
            return response()->json(['status' => 'unknown_bus'], 422);
        }

        $location = BusLocation::create([
            'bus_id' => $bus->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'heading' => $data['heading'] ?? null,
            'speed' => $data['speed'] ?? null,
            'recorded_at' => isset($data['recorded_at']) ? now()->parse($data['recorded_at']) : now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'id' => $location->id,
        ], 201);
    }
}
