<?php

namespace App\Console\Commands;

use App\Events\BusLocationUpdated;
use App\Models\Bus;
use App\Models\BusLocation;
use App\Services\FrotcomClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollFrotcomTracking extends Command
{
    protected $signature = 'tracking:poll-frotcom {--once : Run a single poll and exit} {--sleep=5 : Seconds to sleep between polls}';

    protected $description = 'Poll Frotcom tracking data and store active bus locations.';

    // Cape Town route: Station → Century City via N1/N7
    protected array $mockRoute = [
        [-33.9249, 18.4241],  // Cape Town Station
        [-33.9220, 18.4230],  // Hertzog Blvd
        [-33.9190, 18.4260],  // Foreshore
        [-33.9160, 18.4310],  // Civic Centre
        [-33.9130, 18.4380],  // Nelson Mandela Blvd
        [-33.9080, 18.4420],  // Eastern Blvd
        [-33.9020, 18.4460],  // Woodstock area
        [-33.8950, 18.4500],  // Salt River
        [-33.8880, 18.4520],  // Observatory approach
        [-33.8790, 18.4570],  // Maitland
        [-33.8700, 18.4610],  // Ndabeni
        [-33.8600, 18.4680],  // Pinelands approach
        [-33.8500, 18.4750],  // Mutual
        [-33.8400, 18.4830],  // Langa
        [-33.8300, 18.4920],  // Valkenberg
        [-33.8210, 18.5010],  // N1 approach
        [-33.8140, 18.5100],  // Sable Rd
        [-33.8070, 18.5190],  // Koeberg interchange
        [-33.8000, 18.5280],  // Century City approach
        [-33.8920, 18.5120],  // Ratanga area
        [-33.8870, 18.5150],  // Canal Walk
        [-33.8830, 18.5130],  // Century City station
    ];

    protected int $mockIndex = 0;

    public function handle(FrotcomClient $client): int
    {
        $sleep = max(1, (int) $this->option('sleep'));
        $once = (bool) $this->option('once');
        $mock = (bool) config('services.frotcom.mock_mode', false);

        if ($mock) {
            $this->info('Mock mode enabled — simulating bus movement.');
        }

        do {
            if ($mock) {
                $this->mockPoll();
            } else {
                $this->pollOnce($client);
            }

            if ($once) {
                return self::SUCCESS;
            }

            sleep($sleep);
        } while (true);
    }

    protected function mockPoll(): void
    {
        $buses = Bus::where('active', true)->get();

        if ($buses->isEmpty()) {
            $this->warn('No active buses in database. Run db:seed first.');
            return;
        }

        $createdLocations = collect();
        $routeLen = count($this->mockRoute);

        foreach ($buses as $i => $bus) {
            // Each bus is offset along the route
            $idx = ($this->mockIndex + ($i * 4)) % $routeLen;
            [$lat, $lng] = $this->mockRoute[$idx];

            // Add small random jitter for realism (±0.0003 degrees ≈ 30m)
            $lat += (mt_rand(-30, 30) / 100000);
            $lng += (mt_rand(-30, 30) / 100000);

            // Calculate heading toward next waypoint
            $nextIdx = ($idx + 1) % $routeLen;
            [$nextLat, $nextLng] = $this->mockRoute[$nextIdx];
            $heading = rad2deg(atan2($nextLng - $lng, $nextLat - $lat));
            if ($heading < 0) $heading += 360;

            $speed = mt_rand(25, 55) + (mt_rand(0, 99) / 100);

            $createdLocations->push(BusLocation::create([
                'bus_id' => $bus->id,
                'latitude' => round($lat, 7),
                'longitude' => round($lng, 7),
                'heading' => round($heading, 2),
                'speed' => round($speed, 2),
                'recorded_at' => now(),
            ]));
        }

        $this->mockIndex = ($this->mockIndex + 1) % $routeLen;

        if ($createdLocations->isNotEmpty()) {
            event(new BusLocationUpdated($createdLocations));
            $this->info("Mock: stored {$createdLocations->count()} location(s), waypoint index {$this->mockIndex}.");
        }
    }

    protected function pollOnce(FrotcomClient $client): void
    {
        $typeFilter = trim((string) config('services.frotcom.vehicle_type', ''));
        $vehicleMap = config('services.frotcom.vehicle_map', []);
        $gpsTimezone = (string) config('services.frotcom.last_gps_timezone', 'UTC');

        try {
            $vehicles = $client->getVehicles();
        } catch (\Throwable $e) {
            Log::warning('Frotcom polling failed.', ['error' => $e->getMessage()]);
            $this->warn('Frotcom polling failed: '.$e->getMessage());
            return;
        }

        $createdLocations = collect();

        foreach ($vehicles as $vehicle) {
            if (! is_array($vehicle)) {
                continue;
            }

            // When explicit vehicle mapping is configured, track mapped IDs regardless of typeName.
            if ($typeFilter !== '' && empty($vehicleMap)) {
                $typeName = trim((string) ($vehicle['typeName'] ?? $vehicle['type'] ?? ''));
                if ($typeName === '' || strcasecmp($typeName, $typeFilter) !== 0) {
                    continue;
                }
            }

            if (! $this->isOnTrip($vehicle)) {
                continue;
            }

            $coords = $this->extractCoordinates($vehicle);
            if ($coords === null) {
                continue;
            }

            $bus = $this->resolveBus($vehicle, $vehicleMap);
            if (! $bus || ! $bus->active) {
                continue;
            }

            $recordedAt = $this->parseRecordedAt(
                $vehicle['lastGps'] ?? $vehicle['last_gps'] ?? $vehicle['gpsTime'] ?? $vehicle['gps_time'] ?? null,
                $gpsTimezone
            );

            $latestRecordedAt = BusLocation::query()
                ->where('bus_id', $bus->id)
                ->orderByDesc('recorded_at')
                ->value('recorded_at');

            if ($latestRecordedAt && $recordedAt->lessThanOrEqualTo($latestRecordedAt)) {
                continue;
            }

            $createdLocations->push(BusLocation::create([
                'bus_id' => $bus->id,
                'latitude' => $coords[0],
                'longitude' => $coords[1],
                'heading' => $this->numericOrNull($vehicle['heading'] ?? $vehicle['course'] ?? null),
                'speed' => $this->numericOrNull($vehicle['speed'] ?? null),
                'recorded_at' => $recordedAt,
            ]));
        }

        if ($createdLocations->isNotEmpty()) {
            event(new BusLocationUpdated($createdLocations));
            $this->info("Stored {$createdLocations->count()} bus location(s).");
        }
    }

    protected function isOnTrip(array $vehicle): bool
    {
        $value = $vehicle['isOnTrip'] ?? $vehicle['is_on_trip'] ?? $vehicle['onTrip'] ?? null;

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'y'], true);
        }

        return false;
    }

    protected function extractCoordinates(array $vehicle): ?array
    {
        $lat = $vehicle['latitude'] ?? $vehicle['lat'] ?? $vehicle['latitudeDeg'] ?? null;
        $lng = $vehicle['longitude'] ?? $vehicle['lng'] ?? $vehicle['lon'] ?? $vehicle['longitudeDeg'] ?? null;

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        return [(float) $lat, (float) $lng];
    }

    protected function resolveBus(array $vehicle, array $map): ?Bus
    {
        $candidates = array_filter([
            $vehicle['vehicleId'] ?? $vehicle['vehicleID'] ?? $vehicle['id'] ?? null,
            $vehicle['plate'] ?? $vehicle['licensePlate'] ?? $vehicle['registration'] ?? null,
            $vehicle['name'] ?? null,
            $vehicle['code'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        foreach ($candidates as $candidate) {
            $key = (string) $candidate;

            if (array_key_exists($key, $map)) {
                $mapped = $map[$key];

                if (is_numeric($mapped)) {
                    return Bus::find((int) $mapped);
                }

                return Bus::where('code', $mapped)->first();
            }
        }

        if (empty($map) && ! empty($candidates)) {
            $candidate = (string) $candidates[0];

            return Bus::query()
                ->where('code', $candidate)
                ->orWhere('name', $candidate)
                ->first();
        }

        return null;
    }

    protected function parseRecordedAt(mixed $value, string $timezone): Carbon
    {
        if ($value === null || $value === '') {
            return now();
        }

        if (is_numeric($value)) {
            $timestamp = (int) $value;
            if ($timestamp > 1000000000000) {
                $timestamp = (int) floor($timestamp / 1000);
            }

            return Carbon::createFromTimestampUTC($timestamp);
        }

        try {
            return Carbon::parse($value, $timezone)->utc();
        } catch (\Throwable $e) {
            return now();
        }
    }

    protected function numericOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
