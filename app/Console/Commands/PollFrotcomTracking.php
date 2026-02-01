<?php

namespace App\Console\Commands;

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

    public function handle(FrotcomClient $client): int
    {
        $sleep = max(1, (int) $this->option('sleep'));
        $once = (bool) $this->option('once');

        do {
            $this->pollOnce($client);

            if ($once) {
                return self::SUCCESS;
            }

            sleep($sleep);
        } while (true);
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

        $created = 0;

        foreach ($vehicles as $vehicle) {
            if (! is_array($vehicle)) {
                continue;
            }

            if ($typeFilter !== '') {
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

            BusLocation::create([
                'bus_id' => $bus->id,
                'latitude' => $coords[0],
                'longitude' => $coords[1],
                'heading' => $this->numericOrNull($vehicle['heading'] ?? $vehicle['course'] ?? null),
                'speed' => $this->numericOrNull($vehicle['speed'] ?? null),
                'recorded_at' => $recordedAt,
            ]);

            $created++;
        }

        if ($created > 0) {
            $this->info("Stored {$created} bus location(s).");
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
