<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Collection;

class BusLocationUpdated implements ShouldBroadcastNow
{
    public array $locations;

    public function __construct(Collection $locations)
    {
        // Eager-load bus relationship if not already loaded
        $locations->each(function ($loc) {
            if (! $loc->relationLoaded('bus')) {
                $loc->load('bus:id,code,name');
            }
        });

        $this->locations = $locations->map(function ($loc) {
            $speed = $loc->speed;

            if ($speed !== null && $speed < 3) {
                $status = 'stopped';
            } else {
                $status = 'moving';
            }

            return [
                'bus_id' => $loc->bus_id,
                'bus_name' => $loc->bus?->name ?? ('Bus '.$loc->bus_id),
                'latitude' => $loc->latitude,
                'longitude' => $loc->longitude,
                'heading' => $loc->heading,
                'speed' => $speed !== null ? round($speed, 1) : null,
                'status' => $status,
                'recorded_at' => $loc->recorded_at?->toIso8601String(),
            ];
        })->values()->all();
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tracking');
    }

    public function broadcastAs(): string
    {
        return 'BusLocationUpdated';
    }
}
