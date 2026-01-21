<?php

namespace App\Services;

use App\Models\Chat;

class ChatHistoryService
{
    /**
     * Retrieve full chat history for a session (ordered by time)
     *
     * @param string $sessionId Unique chat session ID
     * @param int|null $limit Optional: limit number of messages (latest)
     * @return string Formatted conversation history
     */
    public function getHistory(string $sessionId, ?int $limit = null): string
    {
        $query = Chat::where('session_id', $sessionId)
            ->orderBy('created_at');

        // If limit provided, get latest messages only
        if ($limit) {
            $query = Chat::where('session_id', $sessionId)
                ->orderByDesc('created_at')
                ->limit($limit);
        }

        $messages = $query->get();

        // If limit was used, reverse to maintain chronological order
        if ($limit) {
            $messages = $messages->reverse();
        }

        // Format history
        $formatted = $messages->map(function ($chat) {
            return ucfirst($chat->role) . ": " . $chat->message;
        });

        return $formatted->implode("\n");
    }
}
