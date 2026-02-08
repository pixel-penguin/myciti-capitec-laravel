<?php

namespace App\Http\Controllers\Api;

use App\Events\BusLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        event(new BusLocationUpdated(collect([$location])));

        return response()->json([
            'status' => 'ok',
            'id' => $location->id,
        ], 201);
    }

    public function latest()
    {
        $latest = BusLocation::query()
            ->select('bus_locations.*')
            ->join(DB::raw('(SELECT bus_id, MAX(recorded_at) AS max_recorded_at FROM bus_locations GROUP BY bus_id) AS latest_locations'), function ($join) {
                $join->on('bus_locations.bus_id', '=', 'latest_locations.bus_id')
                    ->on('bus_locations.recorded_at', '=', 'latest_locations.max_recorded_at');
            })
            ->get();

        return response()->json($latest);
    }
}
