<?php

namespace App\Services;

use App\Models\AiApiConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiApiService
{
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $config = AiApiConfig::where('provider', 'gemini')
            ->where('is_active', true)
            ->first();

        if ($config) {
            $this->apiKey = $config->api_key;
            $this->model = $config->version ?? 'gemini-pro';
        } else {
            // Fallback to environment variables if no config in DB
            $this->apiKey = env('GEMINI_API_KEY');
            $this->model = env('GEMINI_MODEL', 'gemini-pro');
        }
    }

    public function generateResponse(string $prompt): string
    {
        if (!$this->apiKey) {
            Log::error('Gemini API key not configured');
            return 'AI service is not configured. Please contact administrator.';
        }

        try {
            $response = Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }

                Log::error('Unexpected Gemini API response structure', ['response' => $data]);
                return 'I apologize, but I received an unexpected response format.';
            }

            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return 'I apologize, but I\'m having trouble generating a response right now.';

        } catch (\Exception $e) {
            Log::error('Gemini API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 'I apologize, but I\'m experiencing technical difficulties.';
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getModel(): string
    {
        return $this->model;
    }
}