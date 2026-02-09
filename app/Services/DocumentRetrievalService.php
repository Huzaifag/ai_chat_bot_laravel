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
        // Get processing mode from settings
        $processingMode = system_setting('document_processing_mode', 'full');
        
        // Get document chunks
        $chunks = DocumentChunk::where('document_id', $documentId)
            ->orderBy('chunk_index')
            ->get();

        if ($chunks->isEmpty()) {
            return '';
        }

        // Get full context
        $fullContext = $chunks->pluck('chunk_text')->implode("\n\n");
        
        // If context fits within limit, return it
        if (strlen($fullContext) <= $maxChars) {
            return $fullContext;
        }

        // Handle based on processing mode
        if ($processingMode === 'keyword') {
            // Keyword-based retrieval: Find most relevant chunks
            return $this->getKeywordBasedContext($chunks, $userMessage, $maxChars);
        } else {
            // Full mode: Return as much context as possible from the beginning
            return substr($fullContext, 0, $maxChars) . '...';
        }
    }

    /**
     * Get context using keyword-based scoring.
     */
    private function getKeywordBasedContext($chunks, string $userMessage, int $maxChars): string
    {
        $keywords = array_filter(preg_split('/\s+/', strtolower($userMessage)));
        $scoredChunks = [];

        foreach ($chunks as $chunk) {
            $chunkTextLower = strtolower($chunk->chunk_text);
            $score = 0;

            foreach ($keywords as $keyword) {
                if (strlen($keyword) > 2) { // Skip very short words
                    $score += substr_count($chunkTextLower, $keyword);
                }
            }

            if ($score > 0) {
                $scoredChunks[] = [
                    'text' => $chunk->chunk_text,
                    'score' => $score,
                    'index' => $chunk->chunk_index
                ];
            }
        }

        // If no keyword matches, return first chunks
        if (empty($scoredChunks)) {
            $allChunks = $chunks->pluck('chunk_text')->toArray();
            $context = '';
            foreach ($allChunks as $chunkText) {
                if (strlen($context . "\n\n" . $chunkText) <= $maxChars) {
                    $context .= ($context ? "\n\n" : '') . $chunkText;
                } else {
                    break;
                }
            }
            return $context ?: substr($chunks->first()->chunk_text, 0, $maxChars);
        }

        // Sort by score (highest first)
        usort($scoredChunks, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        // Take top scored chunks and build context
        $context = '';
        $usedChunks = 0;
        
        foreach ($scoredChunks as $scoredChunk) {
            if ($usedChunks >= 5) break; // Limit to top 5 chunks
            
            $newContext = $context . ($context ? "\n\n" : '') . $scoredChunk['text'];
            
            if (strlen($newContext) <= $maxChars) {
                $context = $newContext;
                $usedChunks++;
            } else {
                break;
            }
        }

        // If still too long after limiting chunks, truncate
        if (strlen($context) > $maxChars) {
            $context = substr($context, 0, $maxChars) . '...';
        }

        return $context;
    }
}
