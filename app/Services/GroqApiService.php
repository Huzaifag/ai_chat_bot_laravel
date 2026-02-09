<?php

namespace App\Services;

use App\Models\AiApiConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqApiService
{
    protected $apiKey;
    protected $model;
    protected $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        // Attempt to load from database first (following your pattern)
        $config = AiApiConfig::where('provider', 'groq')
            ->where('is_active', true)
            ->first();

        if ($config) {
            $this->apiKey = $config->api_key;
            // Common Groq models: llama-3.3-70b-versatile, llama-3.1-8b-instant
            $this->model = $config->version ?? 'llama-3.3-70b-versatile';
        } else {
            $this->apiKey = env('GROQ_API_KEY');
            $this->model = env('GROQ_MODEL', 'llama-3.3-70b-versatile');
        }
    }

    public function generateResponse(string $prompt): string
    {
        if (!$this->apiKey) {
            Log::error('Groq API key not configured');
            return 'AI service is not configured. Please contact administrator.';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)
                ->post($this->baseUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 1024,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['choices'][0]['message']['content'])) {
                    return $data['choices'][0]['message']['content'];
                }
                Log::error('Unexpected Groq API response structure', ['response' => $data]);
                return 'Unexpected response format from Groq.';
            }

            Log::error('Groq API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return 'The AI service is currently unavailable.';
            
        } catch (\Exception $e) {
            Log::error('Groq API exception', [
                'message' => $e->getMessage()
            ]);
            return 'Technical difficulties with the AI service.';
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}