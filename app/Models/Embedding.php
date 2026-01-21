<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Embedding extends Model
{
    protected $fillable = [
        'chunk_id',
        'vector_id',
    ];

    protected $casts = [
        'document_chunk_id' => 'integer',
    ];

    // Relationship with DocumentChunk
    public function chunk(): BelongsTo
    {
        return $this->belongsTo(DocumentChunk::class, 'chunk_id');
    }

    // Relationship with Document through chunk
    // public function document(): HasOneThrough
    // {
    //     return $this->hasOneThrough(Document::class, DocumentChunk::class, 'id', 'id', 'chunk_id', 'document_id');
    // }
}
