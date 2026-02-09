<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentTextExtractor;
use App\Services\TextChunker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $documentId;

    /**
     * Create a new job instance.
     */
    public function __construct($documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentTextExtractor $textExtractor, TextChunker $textChunker): void
    {
        $document = Document::find($this->documentId);

        if (!$document) {
            Log::error("Document not found: {$this->documentId}");
            return;
        }

        try {
            $document->update(['status' => 'processing']);

            // Extract text
            $text = $textExtractor->extract($document);

            if (empty(trim($text))) {
                $document->update(['status' => 'failed']);
                return;
            }

            // Create chunks using system settings
            $chunks = $textChunker->chunk($text); // Uses default_chunk_size and chunk_overlap from settings

            if (empty($chunks)) {
                $document->update(['status' => 'failed']);
                Log::error('No valid chunks created for document ' . $document->id);
                return;
            }

            // Save chunks
            foreach ($chunks as $index => $chunkContent) {
                $document->chunks()->create([
                    'chunk_index' => $index,
                    'chunk_text' => trim($chunkContent),
                ]);
            }

            $document->update(['status' => 'processed']);
        } catch (\Exception $e) {
            Log::error('Document processing failed for document ' . $document->id . ': ' . $e->getMessage());
            $document->update(['status' => 'failed']);
        }
    }
}


// command to run the worker:

// -> php artisan queue:work