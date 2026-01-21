<?php

namespace App\Services;

use App\Models\AiApiConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiApiService
{
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $config = AiApiConfig::where('provider', 'openai')
            ->where('is_active', true)
            ->first();

        if ($config) {
            $this->apiKey = $config->api_key;
            $this->model = $config->version ?? 'gpt-3.5-turbo';
        } else {
            $this->apiKey = env('OPENAI_API_KEY');
            $this->model = env('OPENAI_MODEL', 'gpt-3.5-turbo');
        }
    }

    public function generateResponse(string $prompt): string
    {
        if (!$this->apiKey) {
            Log::error('OpenAI API key not configured');
            return 'AI service is not configured. Please contact administrator.';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
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
                Log::error('Unexpected OpenAI API response structure', ['response' => $data]);
                return 'I apologize, but I received an unexpected response format.';
            }

            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return 'I apologize, but I\'m having trouble generating a response right now.';
        } catch (\Exception $e) {
            Log::error('OpenAI API exception', [
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
