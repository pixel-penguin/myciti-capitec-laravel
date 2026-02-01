<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ValidatorDevice;
use Illuminate\Support\Facades\Hash;

class EnsureValidatorAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Validator-Key');

        if (! $apiKey) {
            return response()->json(['message' => 'Missing validator key.'], 401);
        }

        $device = ValidatorDevice::query()
            ->where('active', true)
            ->get()
            ->first(function (ValidatorDevice $device) use ($apiKey) {
                return Hash::check($apiKey, $device->api_key_hash);
            });

        if (! $device) {
            return response()->json(['message' => 'Invalid validator key.'], 401);
        }

        $request->attributes->set('validator_device_id', $device->id);

        return $next($request);
    }
}
