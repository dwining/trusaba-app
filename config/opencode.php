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

    // Backend: 'opencode' (session-based MCP) or 'ollama' (OpenAI-compatible chat)
    'backend' => env('AI_BACKEND', 'opencode'),

    // Ollama settings (used when AI_BACKEND=ollama)
    'ollama_endpoint' => env('OLLAMA_ENDPOINT', 'http://localhost:11434'),
    'ollama_model' => env('OLLAMA_MODEL', 'mistral:7b'),

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
Kamu adalah asisten perencana perjalanan wisata profesional untuk traveller Indonesia.
Tugasmu adalah membuat itinerary perjalanan yang personal, realistis, dan menarik.

Format respons HARUS berupa JSON valid sesuai schema yang diberikan.
Jangan tambahkan teks atau penjelasan di luar JSON.
Gunakan bahasa Indonesia untuk semua deskripsi.
PROMPT,

    // JSON schema for itinerary response
    'itinerary_json_schema' => [
        'title' => 'string - judul itinerary',
        'destination' => 'string',
        'total_estimated_budget' => 'integer - total dalam Rupiah',
        'currency' => 'IDR',
        'summary' => 'string - ringkasan singkat perjalanan',
        'days' => [
            [
                'day' => 'integer - nomor hari',
                'date' => 'string - YYYY-MM-DD',
                'theme' => 'string - tema hari ini',
                'schedule' => [
                    [
                        'time' => 'string - HH:MM',
                        'type' => 'enum: hotel|restaurant|attraction|transport|shopping|other',
                        'name' => 'string - nama tempat/layanan',
                        'description' => 'string - deskripsi singkat',
                        'location' => 'string - alamat atau area',
                        'estimated_cost' => 'integer - estimasi biaya dalam Rupiah',
                        'duration_minutes' => 'integer - estimasi durasi',
                        'is_bookable' => 'boolean',
                        'tips' => 'string|null - tips khusus',
                    ],
                ],
                'daily_estimated_cost' => 'integer',
            ],
        ],
        'packing_tips' => ['string'],
        'general_tips' => ['string'],
    ],
];
