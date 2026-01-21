<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Str;

class Document extends Model
{
    protected $fillable = [
        'original_name',
        'stored_path',
        'mime_type',
        'size',
        'status',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'uploaded_by' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($document) {
            // Generate slug from original name
            $slug = Str::slug($document->original_name);

            // Ensure slug is unique
            $count = Document::where('slug', 'LIKE', $slug . '%')->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            $document->slug = $slug;
        });
    }
    // Relationship with Admin (uploader)
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'uploaded_by');
    }

    // Relationship with DocumentChunks
    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }

    // Relationship with Embeddings through chunks
    public function embeddings(): HasManyThrough
    {
        return $this->hasManyThrough(Embedding::class, DocumentChunk::class, 'document_id', 'chunk_id', 'id', 'id');
    }

    // Scope for filtering by status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for filtering by uploader
    public function scopeUploadedBy($query, $adminId)
    {
        return $query->where('uploaded_by', $adminId);
    }
}
