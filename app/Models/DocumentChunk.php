<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentChunk extends Model
{
    protected $fillable = [
        'document_id',
        'chunk_text',
        'chunk_index',
    ];

    protected $casts = [
        'document_id' => 'integer',
        'chunk_index' => 'integer',
    ];

    // Relationship with Document
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    // Relationship with Embeddings
    public function embeddings(): HasMany
    {
        return $this->hasMany(Embedding::class, 'chunk_id');
    }

    // Scope for ordering by chunk index
    public function scopeOrdered($query)
    {
        return $query->orderBy('chunk_index');
    }
}
