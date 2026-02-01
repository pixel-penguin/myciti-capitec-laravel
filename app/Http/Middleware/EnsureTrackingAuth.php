<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TrackingDevice;
use Illuminate\Support\Facades\Hash;

class EnsureTrackingAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Tracking-Key');

        if (! $apiKey) {
            return response()->json(['message' => 'Missing tracking key.'], 401);
        }

        $device = TrackingDevice::query()
            ->where('active', true)
            ->get()
            ->first(function (TrackingDevice $device) use ($apiKey) {
                return Hash::check($apiKey, $device->api_key_hash);
            });

        if (! $device) {
            return response()->json(['message' => 'Invalid tracking key.'], 401);
        }

        $request->attributes->set('tracking_device_id', $device->id);

        return $next($request);
    }
}
