<?php

namespace App\Services;

use App\Models\DocumentChunk;

class DocumentRetrievalService
{
    /**
     * Returns the document context for a given message.
     */
    public function getContext(int $documentId, string $userMessage, int $maxChars = 4000): string
    {
        // Option A: Full context
        $chunks = DocumentChunk::where('document_id', $documentId)
            ->orderBy('chunk_index')
            ->pluck('chunk_text')
            ->toArray();

        $fullContext = implode("\n", $chunks);

        if (strlen($fullContext) <= $maxChars) {
            return $fullContext;
        }

        // Option B: Keyword search fallback
        $keywords = explode(' ', $userMessage);
        $query = DocumentChunk::query();
        foreach ($keywords as $word) {
            $query->orWhere('chunk_text', 'LIKE', "%$word%");
        }

        $relevantChunks = $query->limit(5)->pluck('chunk_text')->toArray();
        return implode("\n", $relevantChunks);
    }
}
