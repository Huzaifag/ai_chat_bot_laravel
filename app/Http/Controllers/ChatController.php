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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected AIPromptService $promptService;
    protected ChatHistoryService $chatHistoryService;
    protected DocumentRetrievalService $documentRetrievalService;

    protected GeminiApiService $geminiApiService;
    protected OpenAiApiService $openAiApiService;

    public function __construct(
        AIPromptService $promptService,
        ChatHistoryService $chatHistoryService,
        DocumentRetrievalService $documentRetrievalService,
        GeminiApiService $geminiApiService,
        OpenAiApiService $openAiApiService
    ) {
        $this->promptService = $promptService;
        $this->chatHistoryService = $chatHistoryService;
        $this->documentRetrievalService = $documentRetrievalService;
        $this->geminiApiService = $geminiApiService;
        $this->openAiApiService = $openAiApiService;
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
            'interest' => 'nullable|string'
        ]);

        $sessionId = $request->session_id;
        $userMessage = trim($request->message);
        $interest = $request->interest;

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

        if ($documents->isNotEmpty()) {
            // Use the first relevant document for context
            $document = $documents->first();
            $context = $this->documentRetrievalService->getContext($document->id, $userMessage);

            // Generate AI prompt
            $prompt = $this->promptService->documentChatPrompt($context, $conversationHistory, $userMessage);

            // Get AI response
            $botResponse = $this->generateAIResponse($prompt);

            // Associate response with document
            $userChat->update(['document_id' => $document->id]);
        }

        // Save bot response
        Chat::create([
            'session_id' => $sessionId,
            'role' => 'bot',
            'message' => $botResponse,
            'document_id' => $documents->isNotEmpty() ? $documents->first()->id : null,
        ]);

        return response()->json([
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

            if ($documents->isNotEmpty()) {
                $document = $documents->first();
                $context = $this->documentRetrievalService->getContext($document->id, $userMessage->message);

                $prompt = $this->promptService->documentChatPrompt($context, $conversationHistory, $userMessage->message);
                $botResponse = $this->generateAIResponse($prompt);

                $userMessage->update(['document_id' => $document->id]);
            }

            // Save bot response
            $botChat = Chat::create([
                'session_id' => $sessionId,
                'role' => 'bot',
                'message' => $botResponse,
                'document_id' => $documents->isNotEmpty() ? $documents->first()->id : null,
            ]);

            $responses[] = [
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
            $keywords = explode(' ', strtolower($message));
            $interestKeywords = [
                'medical' => ['medical', 'health', 'doctor', 'patient', 'treatment'],
                'technical' => ['technical', 'code', 'programming', 'software', 'api'],
                'business' => ['business', 'company', 'finance', 'market', 'sales'],
                'legal' => ['legal', 'law', 'contract', 'agreement', 'regulation'],
                'educational' => ['education', 'learning', 'course', 'training', 'study'],
                'research' => ['research', 'study', 'analysis', 'data', 'experiment']
            ];

            foreach ($interestKeywords as $slug => $words) {
                if (array_intersect($keywords, $words)) {
                    $query->where('slug', $slug);
                    break;
                }
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Generate AI response using configured API
     */
    private function generateAIResponse(string $prompt): string
    {
        try {
            // Get active AI config
            $aiConfig = \App\Models\AiApiConfig::where('is_active', true)->first();
            if (!$aiConfig) {
                return "I'm sorry, but no AI service is currently configured. Please contact the administrator.";
            }

            if ($aiConfig->provider === 'gemini') {
                if (!$this->geminiApiService->isConfigured()) {
                    return "I'm sorry, but Gemini AI service is not configured. Please contact the administrator.";
                }
                return $this->geminiApiService->generateResponse($prompt);
            } elseif ($aiConfig->provider === 'openai') {
                if (!$this->openAiApiService->isConfigured()) {
                    return "I'm sorry, but OpenAI service is not configured. Please contact the administrator.";
                }
                return $this->openAiApiService->generateResponse($prompt);
            } else {
                return "I'm sorry, but the selected AI provider is not supported.";
            }
        } catch (\Exception $e) {
            return "I apologize, but I'm having trouble generating a response right now. Please try again later.";
        }
    }

}
