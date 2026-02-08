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
        $this->locations = $locations->map(fn ($loc) => [
            'bus_id' => $loc->bus_id,
            'latitude' => $loc->latitude,
            'longitude' => $loc->longitude,
            'heading' => $loc->heading,
            'speed' => $loc->speed,
            'recorded_at' => $loc->recorded_at?->toIso8601String(),
        ])->values()->all();
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
