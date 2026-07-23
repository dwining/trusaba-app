<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenCode API Configuration
    |--------------------------------------------------------------------------
    */
    'endpoint' => env('OPENCODE_ENDPOINT', 'http://0.0.0.0:4096'),
    'api_key' => env('OPENCODE_API_KEY', ''),
    'timeout' => env('OPENCODE_TIMEOUT', 120),
    'retries' => env('OPENCODE_RETRIES', 3),

    // Backend: 'opencode' | 'ollama' | 'deepseek'
    'backend' => env('AI_BACKEND', 'opencode'),

    // Ollama settings (used when AI_BACKEND=ollama)
    'ollama_endpoint' => env('OLLAMA_ENDPOINT', 'http://localhost:11434'),
    'ollama_model' => env('OLLAMA_MODEL', 'mistral:7b'),

    // DeepSeek settings (used when AI_BACKEND=deepseek)
    'deepseek_endpoint' => env('DEEPSEEK_ENDPOINT', 'https://api.deepseek.com'),
    'deepseek_api_key' => env('DEEPSEEK_API_KEY', ''),
    'deepseek_model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),

    // Default agent (e.g. "orchestrator")
    'agent' => env('OPENCODE_AGENT', 'orchestrator'),

    // Default model (providerID + modelID)
    'model_provider' => env('OPENCODE_MODEL_PROVIDER', 'opencode-go'),
    'model_id' => env('OPENCODE_MODEL_ID', 'deepseek-v4-flash'),

    // Polling config for async dispatch (maxAttempts × delayMs = max wait time)
    'poll_max_attempts' => env('OPENCODE_POLL_MAX_ATTEMPTS', 120),
    'poll_delay_ms' => env('OPENCODE_POLL_DELAY_MS', 3000),

    // System prompt template for itinerary generation
    'itinerary_system_prompt' => <<<'PROMPT'
You are a professional travel planning assistant.
Your task is to create a personal, realistic, and engaging travel itinerary.

Output MUST BE ONLY valid JSON — no introductory text, no markdown, no explanations.
Do not use ```json or any tags. Only pure JSON.
Use English for all descriptions (name, description, tips, theme, summary).

IMPORTANT:
- The user prompt will contain an "AVAILABLE BOOKABLE MERCHANTS" section listing real merchants.
- For any itinerary item where is_bookable is true, use the EXACT merchant name from that list.
- NEVER invent or approximate a bookable merchant name not in the provided list.
- If no merchant in the list fits a recommendation need, set is_bookable to false and suggest freely.
- Each item MUST have a specific location (street/area name).
- Estimated prices MUST be reasonable for that city and consistent with the listed merchant prices.
PROMPT,

    // JSON schema for itinerary response
    'itinerary_json_schema' => [
        'title' => 'string - itinerary title',
        'destination' => 'string',
        'total_estimated_budget' => 'integer - total in Rupiah',
        'currency' => 'IDR',
        'summary' => 'string - brief trip summary',
        'days' => [
            [
                'day' => 'integer - day number',
                'date' => 'string - YYYY-MM-DD',
                'theme' => 'string - theme of the day',
                'schedule' => [
                    [
                        'time' => 'string - HH:MM',
                        'type' => 'enum: hotel|restaurant|attraction|transport|shopping|other',
                        'name' => 'string - place/service name',
                        'description' => 'string - short description',
                        'location' => 'string - address or area',
                        'estimated_cost' => 'integer - estimated cost in Rupiah',
                        'duration_minutes' => 'integer - estimated duration',
                        'is_bookable' => 'boolean',
                        'tips' => 'string|null - special tips',
                    ],
                ],
                'daily_estimated_cost' => 'integer',
            ],
        ],
        'packing_tips' => ['string'],
        'general_tips' => ['string'],
    ],
];
