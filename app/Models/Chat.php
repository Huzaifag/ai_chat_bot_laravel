<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'document_id',
        'role',
        'message',
        'api_provider',
        'api_tokens_used',
        'api_cost',
        'response_time_ms',
    ];

    /**
     * Belongs to a chat session
     */
    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id', 'session_id');
    }

    /**
     * Belongs to a document (optional)
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Has one bot response (for user messages)
     */
    public function botResponse()
    {
        return $this->hasOne(Chat::class, 'session_id', 'session_id')
            ->where('role', 'bot')
            ->whereRaw('created_at > (SELECT created_at FROM chats c2 WHERE c2.id = chats.id AND c2.role = "user")')
            ->orderBy('created_at', 'asc')
            ->limit(1);
    }

    /**
     * Helper: check if message is from user
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Helper: check if message is from bot
     */
    public function isBot()
    {
        return $this->role === 'bot';
    }
}
