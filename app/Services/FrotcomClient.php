<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FrotcomClient
{
    public function getVehicles(): array
    {
        $baseUrl = $this->baseUrl();
        $apiKey = $this->getApiKey();

        $response = Http::retry(2, 250)
            ->timeout(15)
            ->get("{$baseUrl}/v2/vehicles", [
                'api_key' => $apiKey,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Frotcom vehicles request failed with status '.$response->status().'.');
        }

        return $this->extractVehicles($response->json());
    }

    protected function getApiKey(): string
    {
        $apiKey = (string) config('services.frotcom.api_key', '');
        if ($apiKey !== '') {
            return $apiKey;
        }

        $ttl = (int) config('services.frotcom.token_ttl', 50);

        return Cache::remember('frotcom.api_key', now()->addMinutes($ttl), function () {
            $baseUrl = $this->baseUrl();
            $username = (string) config('services.frotcom.username', '');
            $password = (string) config('services.frotcom.password', '');

            if ($username === '' || $password === '') {
                throw new RuntimeException('Frotcom credentials are not configured.');
            }

            $response = Http::retry(2, 250)
                ->timeout(15)
                ->post("{$baseUrl}/v2/authorize", [
                    'provider' => 'thirdparty',
                    'username' => $username,
                    'password' => $password,
                ]);

            if (! $response->successful()) {
                throw new RuntimeException('Frotcom authorize request failed with status '.$response->status().'.');
            }

            $token = $this->extractToken($response->json());

            if ($token === null) {
                throw new RuntimeException('Frotcom authorize response did not include an API key.');
            }

            return $token;
        });
    }

    protected function baseUrl(): string
    {
        $baseUrl = rtrim((string) config('services.frotcom.base_url', ''), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('Frotcom base URL is not configured.');
        }

        return $baseUrl;
    }

    protected function extractToken(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        $candidates = [
            $payload['api_key'] ?? null,
            $payload['apiKey'] ?? null,
            $payload['token'] ?? null,
            $payload['accessToken'] ?? null,
            $payload['access_token'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    protected function extractVehicles(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['data', 'vehicles', 'items', 'result', 'results'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return [];
    }
}
