<?php

namespace App\Services;

class TextChunker
{
    /**
     * Split text into chunks based on system settings.
     *
     * @param string $text
     * @param int|null $chunkSize Number of characters per chunk (null = use settings)
     * @param int|null $overlap Number of characters to overlap (null = use settings)
     */
    public function chunk(string $text, ?int $chunkSize = null, ?int $overlap = null): array
    {
        // Get settings if not provided
        $chunkSize = $chunkSize ?? (int) system_setting('default_chunk_size', '1000');
        $overlap = $overlap ?? (int) system_setting('chunk_overlap', '200');

        $chunks = [];
        $text = trim($text);
        
        if (empty($text)) {
            return [];
        }

        // Character-based chunking with overlap
        $length = strlen($text);
        
        for ($i = 0; $i < $length; $i += ($chunkSize - $overlap)) {
            $chunk = substr($text, $i, $chunkSize);
            
            if (strlen(trim($chunk)) > 0) {
                $chunks[] = $chunk;
            }
        }

        return array_filter($chunks);
    }
}
