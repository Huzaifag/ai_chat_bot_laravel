<?php

namespace App\Http\Controllers;

use App\Models\AiApiConfig;
use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\Document;
use App\Services\AIPromptService;
use App\Services\ChatHistoryService;
use App\Services\DocumentRetrievalService;
use App\Services\GeminiApiService;
use App\Services\OpenAiApiService;
use App\Services\GroqApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Log;

class ChatController extends Controller
{
    protected AIPromptService $promptService;
    protected ChatHistoryService $chatHistoryService;
    protected DocumentRetrievalService $documentRetrievalService;

    protected GeminiApiService $geminiApiService;
    protected OpenAiApiService $openAiApiService;
    protected GroqApiService $groqApiService;

    public function __construct(
        AIPromptService $promptService,
        ChatHistoryService $chatHistoryService,
        DocumentRetrievalService $documentRetrievalService,
        GeminiApiService $geminiApiService,
        OpenAiApiService $openAiApiService,
        GroqApiService $groqApiService
    ) {
        $this->promptService = $promptService;
        $this->chatHistoryService = $chatHistoryService;
        $this->documentRetrievalService = $documentRetrievalService;
        $this->geminiApiService = $geminiApiService;
        $this->openAiApiService = $openAiApiService;
        $this->groqApiService = $groqApiService;
    }

    /**
     * Get all messages for a chat session
     */
    public function getMessages(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $sessionId = $request->session_id;

        $messages = Chat::where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'role' => $chat->role,
                    'message' => $chat->message,
                    'created_at' => $chat->created_at,
                    'document_id' => $chat->document_id
                ];
            });

        return response()->json([
            'messages' => $messages,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Send a message and get AI response
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string|max:1000',
            'interest' => 'nullable|string',
            'mode' => 'nullable|in:default,research'
        ]);

        $sessionId = $request->session_id;
        $userMessage = trim($request->message);
        $interest = $request->interest;
        $mode = $request->mode ?? 'default';

        // Ensure session exists
        $session = ChatSession::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_id' => null] // Anonymous for now
        );

        // Save user message
        $userChat = Chat::create([
            'session_id' => $sessionId,
            'role' => 'user',
            'message' => $userMessage,
        ]);

        // Get conversation history
        $conversationHistory = $this->chatHistoryService->getHistory($sessionId, 10);

        // Find relevant documents based on interest
        $documents = $this->findRelevantDocuments($interest, $userMessage);

        $botResponse = "I understand you're asking about: " . $userMessage;
        $apiProvider = null;
        $apiTokens = 0;
        $apiCost = 0;
        $responseTimeMs = 0;

        if ($documents->isNotEmpty()) {
            // Use the first relevant document for context
            $document = $documents->first();
            $context = $this->documentRetrievalService->getContext($document->id, $userMessage);

            // Generate AI prompt based on mode
            if ($mode === 'research') {
                $prompt = $this->promptService->researchChatPrompt($context, $conversationHistory, $userMessage);
            } else {
                $prompt = $this->promptService->documentChatPrompt($context, $conversationHistory, $userMessage);
            }

            // Get AI response with metrics
            $startTime = microtime(true);
            $aiResult = $this->generateAIResponse($prompt);
            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);
            
            $botResponse = $aiResult['response'];
            $apiProvider = $aiResult['provider'];
            $apiTokens = $aiResult['tokens'];
            $apiCost = $aiResult['cost'];

            // Associate response with document
            $userChat->update(['document_id' => $document->id]);
        }

        // Save bot response with API metrics
        Chat::create([
            'session_id' => $sessionId,
            'role' => 'bot',
            'message' => $botResponse,
            'document_id' => $documents->isNotEmpty() ? $documents->first()->id : null,
            'api_provider' => $apiProvider,
            'api_tokens_used' => $apiTokens,
            'api_cost' => $apiCost,
            'response_time_ms' => $responseTimeMs,
        ]);

        return response()->json([
            'interest' => $interest,
            'message' => $botResponse,
            'session_id' => $sessionId,
            'document_used' => $documents->isNotEmpty() ? $documents->first()->original_name : null
        ]);
    }

    /**
     * Process all pending messages in a session (batch processing)
     */
    public function processSessionMessages(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'interest' => 'nullable|string'
        ]);

        $sessionId = $request->session_id;
        $interest = $request->interest;

        // Get all user messages that don't have bot responses yet
        $userMessages = Chat::where('session_id', $sessionId)
            ->where('role', 'user')
            ->whereDoesntHave('botResponse')
            ->orderBy('created_at')
            ->get();

        $responses = [];

        foreach ($userMessages as $userMessage) {
            // Get conversation history up to this point
            $conversationHistory = $this->chatHistoryService->getHistory($sessionId, 10);

            // Find relevant documents
            $documents = $this->findRelevantDocuments($interest, $userMessage->message);

            $botResponse = "I received your message: " . $userMessage->message;
            $apiProvider = null;
            $apiTokens = 0;
            $apiCost = 0;
            $responseTimeMs = 0;

            if ($documents->isNotEmpty()) {
                $document = $documents->first();
                $context = $this->documentRetrievalService->getContext($document->id, $userMessage->message);

                $prompt = $this->promptService->documentChatPrompt($context, $conversationHistory, $userMessage->message);
                
                $startTime = microtime(true);
                $aiResult = $this->generateAIResponse($prompt);
                $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);
                
                $botResponse = $aiResult['response'];
                $apiProvider = $aiResult['provider'];
                $apiTokens = $aiResult['tokens'];
                $apiCost = $aiResult['cost'];

                $userMessage->update(['document_id' => $document->id]);
            }

            // Save bot response with metrics
            $botChat = Chat::create([
                'session_id' => $sessionId,
                'role' => 'bot',
                'message' => $botResponse,
                'document_id' => $documents->isNotEmpty() ? $documents->first()->id : null,
                'api_provider' => $apiProvider,
                'api_tokens_used' => $apiTokens,
                'api_cost' => $apiCost,
                'response_time_ms' => $responseTimeMs,
            ]);

            $responses[] = [
                'interest' => $interest,
                'user_message_id' => $userMessage->id,
                'bot_message' => $botResponse,
                'document_used' => $documents->isNotEmpty() ? $documents->first()->original_name : null
            ];
        }

        return response()->json([
            'processed' => count($responses),
            'responses' => $responses
        ]);
    }

    /**
     * Find relevant documents based on interest and message content
     */
    private function findRelevantDocuments(?string $interest, string $message)
    {
        $query = Document::where('status', 'processed');

        // Filter by interest if provided
        if ($interest && $interest !== 'general') {
            $query->where('slug', $interest);
        }

        // If no specific interest, try to match keywords in message
        if (!$interest || $interest === 'general') {
             $query->where('slug', 'general');
            // $keywords = explode(' ', strtolower($message));
            // $interestKeywords = [
            //     'medical' => ['medical', 'health', 'doctor', 'patient', 'treatment'],
            //     'technical' => ['technical', 'code', 'programming', 'software', 'api' , 'artificial_intelligence', 'machine_learning'],
            //     'business' => ['business', 'company', 'finance', 'market', 'sales'],
            //     'legal' => ['legal', 'law', 'contract', 'agreement', 'regulation'],
            //     'educational' => ['education', 'learning', 'course', 'training', 'study'],
            //     'research' => ['research', 'study', 'analysis', 'data', 'experiment']
            // ];

            // Log::info('Extracted keywords: ' . implode(', ', $keywords));
            // foreach ($interestKeywords as $slug => $words) {
            //     if (array_intersect($keywords, $words)) {
            //         $query->where('slug', $slug);
            //         break;
            //     }
            // }


        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Generate AI response using configured API
     * Returns array with response, provider, tokens, and cost
     */
    private function generateAIResponse(string $prompt): array
    {
        try {
            // Get active AI config
            $aiConfig = AiApiConfig::where('is_active', true)->first();
            if (!$aiConfig) {
                return [
                    'response' => "I'm sorry, but no AI service is currently configured. Please contact the administrator.",
                    'provider' => null,
                    'tokens' => 0,
                    'cost' => 0
                ];
            }

            if ($aiConfig->provider === 'gemini') {
                if (!$this->geminiApiService->isConfigured()) {
                    return [
                        'response' => "I'm sorry, but Gemini AI service is not configured. Please contact the administrator.",
                        'provider' => 'gemini',
                        'tokens' => 0,
                        'cost' => 0
                    ];
                }
                $response = $this->geminiApiService->generateResponse($prompt);
                // Estimate tokens (rough estimate: 1 token ≈ 4 characters)
                $tokens = (int)(strlen($prompt . $response) / 4);
                // Gemini pricing (example: $0.00025 per 1K tokens)
                $cost = ($tokens / 1000) * 0.00025;
                
                return [
                    'response' => $response,
                    'provider' => 'gemini',
                    'tokens' => $tokens,
                    'cost' => $cost
                ];
            } elseif ($aiConfig->provider === 'openai') {
                if (!$this->openAiApiService->isConfigured()) {
                    return [
                        'response' => "I'm sorry, but OpenAI service is not configured. Please contact the administrator.",
                        'provider' => 'openai',
                        'tokens' => 0,
                        'cost' => 0
                    ];
                }
                $response = $this->openAiApiService->generateResponse($prompt);
                // Estimate tokens
                $tokens = (int)(strlen($prompt . $response) / 4);
                // OpenAI pricing (example: $0.002 per 1K tokens for GPT-3.5)
                $cost = ($tokens / 1000) * 0.002;
                return [
                    'response' => $response,
                    'provider' => 'openai',
                    'tokens' => $tokens,
                    'cost' => $cost
                ];
            } elseif ($aiConfig->provider === 'groq') {
                if (!$this->groqApiService->isConfigured()) {
                    return [
                        'response' => "I'm sorry, but Groq AI service is not configured. Please contact the administrator.",
                        'provider' => 'groq',
                        'tokens' => 0,
                        'cost' => 0
                    ];
                }
                $response = $this->groqApiService->generateResponse($prompt);
                // Estimate tokens
                $tokens = (int)(strlen($prompt . $response) / 4);
                // Groq pricing (example: $0.0015 per 1K tokens for GPT-3.5)
                $cost = ($tokens / 1000) * 0.0015;
                
                return [
                    'response' => $response,
                    'provider' => 'groq',
                    'tokens' => $tokens,
                    'cost' => $cost
                ];
            } else {
                return [
                    'response' => "I'm sorry, but the selected AI provider is not supported.",
                    'provider' => null,
                    'tokens' => 0,
                    'cost' => 0
                ];
            }
        } catch (\Exception $e) {
            return [
                'response' => "I apologize, but I'm having trouble generating a response right now. Please try again later.",
                'provider' => null,
                'tokens' => 0,
                'cost' => 0
            ];
        }
    }

}
