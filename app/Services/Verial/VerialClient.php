<?php

declare(strict_types=1);

namespace App\Services\Verial;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerialClient
{
    public function __construct(
        private readonly string|null $host,
        private readonly int $port,
        private readonly string|null $session,
        private readonly int $timeout,
    ) {}

    public function isConfigured(): bool
    {
        return $this->host !== null && $this->session !== null;
    }

    /**
     * Realiza una petición GET al conector Verial.
     *
     * @throws \RuntimeException
     */
    public function get(string $method, array $params = []): array
    {
        $this->ensureConfigured();

        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl($method), $this->withSession($params));

            $response->throw();

            return $response->json() ?? [];
        } catch (ConnectionException $e) {
            Log::error('Verial connection error on GET', [
                'method' => $method,
                'error'  => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Error de conexión con Verial: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Realiza una petición POST al conector Verial con cuerpo JSON.
     *
     * @throws \RuntimeException
     */
    public function post(string $method, array $body = []): array
    {
        $this->ensureConfigured();

        try {
            $response = Http::timeout($this->timeout)
                ->withQueryParameters($this->withSession())
                ->post($this->baseUrl($method), $body);

            $response->throw();

            return $response->json() ?? [];
        } catch (ConnectionException $e) {
            Log::error('Verial connection error on POST', [
                'method' => $method,
                'error'  => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Error de conexión con Verial: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    private function baseUrl(string $method): string
    {
        return "http://{$this->host}:{$this->port}/{$method}";
    }

    private function withSession(array $params = []): array
    {
        return array_merge(['x' => $this->session], $params);
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException(
                'Verial no configurado. Configura VERIAL_HOST y VERIAL_SESSION en .env.'
            );
        }
    }
}
