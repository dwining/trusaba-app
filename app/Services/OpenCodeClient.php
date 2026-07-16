<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class OpenCodeClient
{
    protected string $endpoint;
    protected int $timeout;
    protected int $retries;

    public function __construct()
    {
        $this->endpoint = config('opencode.endpoint', 'http://0.0.0.0:4096');
        $this->timeout = config('opencode.timeout', 120);
        $this->retries = config('opencode.retries', 3);
    }

    /**
     * Send a prompt to OpenCode and get structured JSON response.
     */
    public function generateItinerary(array $payload): array
    {
        $response = Http::timeout($this->timeout)
            ->retry($this->retries, function (int $attempt) {
                return $attempt * 30000; // 30s, 60s, 90s backoff in ms
            })
            ->post($this->endpoint, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'OpenCode request failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        $data = $response->json();

        if (!isset($data['itinerary']) && !isset($data['days'])) {
            // Try to extract JSON from response if wrapped in text
            return $this->extractJson($response);
        }

        return $data;
    }

    /**
     * Send a chat message to AI customer service.
     */
    public function chat(string $message, array $context = []): string
    {
        $response = Http::timeout($this->timeout)
            ->post($this->endpoint, [
                'message' => $message,
                'context' => $context,
            ]);

        return $response->body();
    }

    /**
     * Ping the OpenCode server to check connectivity.
     */
    public function ping(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->endpoint);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Attempt to extract JSON from a response that may have surrounding text.
     */
    protected function extractJson(Response $response): array
    {
        $body = $response->body();
        
        // Try to find JSON block in the response
        if (preg_match('/\{[\s\S]*\}/', $body, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return ['raw_response' => $body];
    }
}
