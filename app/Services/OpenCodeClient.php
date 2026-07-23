<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenCodeClient
{
    protected string $endpoint;

    protected string $apiKey;

    protected int $timeout;

    protected int $retries;

    protected string $defaultAgent;

    protected ?array $defaultModel;

    protected int $pollMaxAttempts;

    protected int $pollDelayMs;

    public function __construct()
    {
        $this->endpoint = rtrim(config('opencode.endpoint', 'http://0.0.0.0:4096'), '/');
        $this->apiKey = config('opencode.api_key', '');
        $this->timeout = config('opencode.timeout', 120);
        $this->retries = config('opencode.retries', 3);
        $this->defaultAgent = config('opencode.agent', 'orchestrator');

        $providerId = config('opencode.model_provider');
        $modelId = config('opencode.model_id');

        $this->defaultModel = ($providerId && $modelId)
            ? ['providerID' => $providerId, 'modelID' => $modelId]
            : null;

        $this->pollMaxAttempts = config('opencode.poll_max_attempts', 120);
        $this->pollDelayMs = config('opencode.poll_delay_ms', 3000);
    }

    /**
     * Whether we're using Ollama as the AI backend.
     */
    public function isOllamaBackend(): bool
    {
        return config('opencode.backend') === 'ollama';
    }

    /**
     * Whether we're using DeepSeek as the AI backend.
     */
    public function isDeepSeekBackend(): bool
    {
        return config('opencode.backend') === 'deepseek';
    }

    // ─── Session API ────────────────────────────────────────────────────

    /**
     * Create a new OpenCode session.
     * POST /session
     */
    public function createSession(): array
    {
        $response = $this->http()->post("{$this->endpoint}/session", (object) []);

        $this->ensureSuccessful($response, 'Failed to create session');

        return $response->json();
    }

    /**
     * Dispatch a single text prompt to an existing session (async — returns immediately).
     * POST /session/{id}/prompt_async
     */
    public function dispatchPrompt(
        string $sessionId,
        string $prompt,
        ?string $agent = null,
        ?array $model = null
    ): void {
        $payload = $this->buildPromptPayload($prompt, $agent, $model);

        $response = $this->http()->post(
            "{$this->endpoint}/session/{$sessionId}/prompt_async",
            $payload
        );

        $this->ensureSuccessful($response, 'Failed to dispatch prompt');
    }

    /**
     * Dispatch a prompt with separate system + user parts.
     * POST /session/{id}/prompt_async
     */
    public function dispatchPromptWithSystem(
        string $sessionId,
        string $systemPrompt,
        string $userPrompt,
        ?string $agent = null,
        ?array $model = null
    ): void {
        $payload = [
            'agent' => $agent ?? $this->defaultAgent,
            'parts' => [
                ['type' => 'text', 'text' => $systemPrompt],
                ['type' => 'text', 'text' => $userPrompt],
            ],
        ];

        if ($model ?? $this->defaultModel) {
            $payload['model'] = $model ?? $this->defaultModel;
        }

        $response = $this->http()->post(
            "{$this->endpoint}/session/{$sessionId}/prompt_async",
            $payload
        );

        $this->ensureSuccessful($response, 'Failed to dispatch prompt');
    }

    /**
     * Get all messages from a session.
     * GET /session/{id}/message
     *
     * Returns array of message objects with info.role, info.finish, info.error, parts[].
     */
    public function getMessages(string $sessionId): array
    {
        $response = $this->http()->get("{$this->endpoint}/session/{$sessionId}/message");

        $this->ensureSuccessful($response, 'Failed to get messages');

        return $response->json();
    }

    /**
     * Delete a session to free resources.
     * DELETE /session/{id}
     */
    public function deleteSession(string $sessionId): bool
    {
        $response = $this->http()->delete("{$this->endpoint}/session/{$sessionId}");

        return $response->successful();
    }

    // ─── Convenience Flows ──────────────────────────────────────────────

    /**
     * Poll for assistant completion.
     * Waits until the assistant finishes (finish == "stop") and returns concatenated text.
     * Throws on error.
     */
    public function pollForCompletion(string $sessionId, ?int $maxAttempts = null, ?int $delayMs = null): string
    {
        $maxAttempts ??= $this->pollMaxAttempts;
        $delayMs ??= $this->pollDelayMs;

        $allText = '';

        for ($i = 0; $i < $maxAttempts; $i++) {
            usleep($delayMs * 1000);

            $messages = $this->getMessages($sessionId);
            $assistantMsgs = $this->extractAssistantMessages($messages);
            $lastFinish = $this->lastFinish($messages);

            // Collect partial text as it arrives
            $allText = $this->concatText($assistantMsgs);

            if ($lastFinish === 'stop') {
                return $allText;
            }

            if ($this->hasError($messages)) {
                $error = $this->firstError($messages);
                throw new \RuntimeException(
                    'OpenCode error: '.($error['data']['message'] ?? $error['name'] ?? 'Unknown error')
                );
            }
        }

        Log::warning('OpenCode pollForCompletion timed out', [
            'session_id' => $sessionId,
            'attempts' => $maxAttempts,
        ]);

        // Timeout — return what we collected so far
        return $allText;
    }

    /**
     * Synchronous dispatch: create session → dispatch → poll → delete → return text.
     */
    public function dispatchPromptSync(string $prompt, ?string $agent = null, ?array $model = null): string
    {
        $session = $this->createSession();
        $sessionId = $session['id'] ?? ($session['session_id']
            ?? throw new \RuntimeException('No session ID in response'));

        try {
            $this->dispatchPrompt($sessionId, $prompt, $agent, $model);

            return $this->pollForCompletion($sessionId);
        } finally {
            $this->deleteSession($sessionId);
        }
    }

    /**
     * Synchronous dispatch with system prompt.
     */
    public function dispatchPromptSyncWithSystem(
        string $systemPrompt,
        string $userPrompt,
        ?string $agent = null,
        ?array $model = null
    ): string {
        $session = $this->createSession();
        $sessionId = $session['id'] ?? ($session['session_id']
            ?? throw new \RuntimeException('No session ID in response'));

        try {
            $this->dispatchPromptWithSystem($sessionId, $systemPrompt, $userPrompt, $agent, $model);

            return $this->pollForCompletion($sessionId);
        } finally {
            $this->deleteSession($sessionId);
        }
    }

    /**
     * Async pattern step 1: create session + dispatch prompt, return session ID.
     * The caller stores the session ID and polls later (e.g. from a second queue job).
     */
    public function createSessionAndDispatch(
        string $prompt,
        ?string $title = null,
        ?string $agent = null,
        ?array $model = null
    ): string {
        $session = $this->createSession();
        $sessionId = $session['id'] ?? ($session['session_id']
            ?? throw new \RuntimeException('No session ID in response'));

        $this->dispatchPrompt($sessionId, $prompt, $agent, $model);

        return $sessionId;
    }

    // ─── Domain Methods ─────────────────────────────────────────────────

    /**
     * Generate an itinerary via OpenCode (sync flow).
     * Returns the raw assistant text — caller is responsible for JSON parsing.
     */
    public function generateItinerary(
        string $systemPrompt,
        string $userPrompt,
        ?string $agent = null,
        ?array $model = null
    ): string {
        if ($this->isOllamaBackend()) {
            return $this->generateItineraryViaOllama($systemPrompt, $userPrompt);
        }

        if ($this->isDeepSeekBackend()) {
            return $this->generateItineraryViaDeepSeek($systemPrompt, $userPrompt);
        }

        return $this->dispatchPromptSyncWithSystem($systemPrompt, $userPrompt, $agent, $model);
    }

    /**
     * Send a chat message to AI customer service (sync flow).
     */
    public function chat(string $message, array $context = []): string
    {
        if ($this->isOllamaBackend()) {
            return $this->chatViaOllama($message, $context);
        }

        if ($this->isDeepSeekBackend()) {
            return $this->chatViaDeepSeek($message, $context);
        }

        if (! empty($context)) {
            $message .= "\n\nContext:\n".json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return $this->dispatchPromptSync($message);
    }

    // ─── Ollama Backend ──────────────────────────────────────────────────

    protected function generateItineraryViaOllama(string $systemPrompt, string $userPrompt): string
    {
        $ollamaEndpoint = rtrim(config('opencode.ollama_endpoint'), '/');
        $model = config('opencode.ollama_model', 'mistral:7b');

        $response = Http::timeout($this->timeout)
            ->post("{$ollamaEndpoint}/api/chat", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'format' => 'json',
                'stream' => false,
                'options' => ['temperature' => 0.7],
            ]);

        return $response->json()['message']['content'] ?? '';
    }

    protected function chatViaOllama(string $message, array $context = []): string
    {
        $ollamaEndpoint = rtrim(config('opencode.ollama_endpoint'), '/');
        $model = config('opencode.ollama_model', 'mistral:7b');

        $systemPrompt = "You are TruSaba's friendly customer service.\nYou help travellers with itinerary questions, destination information, booking, payment, and travel tips.\nRespond in English, friendly and concise.\n\nFormat your answers cleanly:\n- Use line breaks between paragraphs.\n- Use \"-\" for bullet points when listing multiple items.\n- Use **bold** to highlight key names and places.\n- Keep responses warm and conversational.";

        if (! empty($context)) {
            $systemPrompt .= "\n\nTraveller context:\n".json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $response = Http::timeout($this->timeout)
            ->post("{$ollamaEndpoint}/api/chat", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'stream' => false,
                'options' => ['temperature' => 0.8],
            ]);

        return $response->json()['message']['content'] ?? 'Sorry, something went wrong. Please try again.';
    }

    // ─── DeepSeek Backend ───────────────────────────────────────────────

    protected function generateItineraryViaDeepSeek(string $systemPrompt, string $userPrompt): string
    {
        $endpoint = rtrim(config('opencode.deepseek_endpoint'), '/');
        $model = config('opencode.deepseek_model', 'deepseek-chat');
        $apiKey = config('opencode.deepseek_api_key');

        $response = Http::timeout($this->timeout)
            ->withToken($apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->post("{$endpoint}/v1/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 4096,
            ]);

        return $response->json()['choices'][0]['message']['content'] ?? '';
    }

    protected function chatViaDeepSeek(string $message, array $context = []): string
    {
        $endpoint = rtrim(config('opencode.deepseek_endpoint'), '/');
        $model = config('opencode.deepseek_model', 'deepseek-chat');
        $apiKey = config('opencode.deepseek_api_key');

        $systemPrompt = "You are TruSaba's friendly customer service.\nYou help travellers with itinerary questions, destination information, booking, payment, and travel tips.\nRespond in English, friendly and concise.\n\nFormat your answers cleanly:\n- Use line breaks between paragraphs.\n- Use \"-\" for bullet points when listing multiple items.\n- Use **bold** to highlight key names and places.\n- Keep responses warm and conversational.";

        if (! empty($context)) {
            $systemPrompt .= "\n\nTraveller context:\n".json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $response = Http::timeout($this->timeout)
            ->withToken($apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->post("{$endpoint}/v1/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.8,
                'max_tokens' => 1024,
            ]);

        return $response->json()['choices'][0]['message']['content'] ?? 'Sorry, something went wrong. Please try again.';
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

    // ─── JSON Extraction ────────────────────────────────────────────────

    /**
     * Extract a JSON object from a text response.
     */
    public function extractJsonFromText(string $text): array
    {
        // Strip markdown code fences if present
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*\n?/i', '', $text);
        $text = preg_replace('/\n?```\s*$/i', '', $text);
        $text = trim($text);

        // Sanitize: remove control characters that break JSON parsing
        // (except \n, \r, \t which are valid whitespace)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Try direct JSON parse first (expected pure JSON)
        $decoded = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Fallback: try to extract JSON from mixed text
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return ['raw_response' => $text];
    }

    // ─── Internal Helpers ───────────────────────────────────────────────

    protected function http(): PendingRequest
    {
        $request = Http::timeout($this->timeout)
            ->retry($this->retries, function (int $attempt) {
                return $attempt * 30000;
            })
            ->acceptJson()
            ->asJson();

        if ($this->apiKey) {
            $request->withToken($this->apiKey);
        }

        return $request;
    }

    protected function ensureSuccessful(Response $response, string $message): void
    {
        if (! $response->successful()) {
            throw new \RuntimeException(
                "{$message}: {$response->status()} - ".$response->body()
            );
        }
    }

    protected function buildPromptPayload(string $prompt, ?string $agent, ?array $model): array
    {
        $payload = [
            'agent' => $agent ?? $this->defaultAgent,
            'parts' => [
                ['type' => 'text', 'text' => $prompt],
            ],
        ];

        if ($model ?? $this->defaultModel) {
            $payload['model'] = $model ?? $this->defaultModel;
        }

        return $payload;
    }

    /**
     * Filter messages array to only assistant role entries.
     */
    protected function extractAssistantMessages(array $messages): array
    {
        return array_values(array_filter($messages, function (array $msg) {
            return ($msg['info']['role'] ?? '') === 'assistant';
        }));
    }

    /**
     * Get the finish state of the last message in the array.
     */
    protected function lastFinish(array $messages): string
    {
        if (empty($messages)) {
            return '';
        }

        $last = end($messages);

        return $last['info']['finish'] ?? '';
    }

    /**
     * Check if any message has an error.
     */
    protected function hasError(array $messages): bool
    {
        foreach ($messages as $msg) {
            if (! empty($msg['info']['error'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the first error payload from messages.
     */
    protected function firstError(array $messages): ?array
    {
        foreach ($messages as $msg) {
            if (! empty($msg['info']['error'])) {
                return $msg['info']['error'];
            }
        }

        return null;
    }

    /**
     * Concatenate all text parts from assistant messages into one string.
     */
    protected function concatText(array $assistantMessages): string
    {
        $texts = [];

        foreach ($assistantMessages as $msg) {
            foreach ($msg['parts'] ?? [] as $part) {
                if (($part['type'] ?? '') === 'text') {
                    $texts[] = $part['text'] ?? '';
                }
            }
        }

        return implode("\n", $texts);
    }
}
