<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'current_document_id',
        'user_id',
    ];

    /**
     * Each session may have many chat messages
     */
    public function chats()
    {
        return $this->hasMany(Chat::class, 'session_id', 'session_id');
    }

    /**
     * Current document of the session
     */
    public function currentDocument()
    {
        return $this->belongsTo(Document::class, 'current_document_id');
    }

    /**
     * Optional relation to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
