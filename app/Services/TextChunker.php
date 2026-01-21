<?php

namespace App\Services;

class TextChunker
{
    /**
     * Split text into chunks.
     *
     * @param string $text
     * @param int $chunkSize Number of words per chunk
     * @param int $overlap Number of words to overlap between chunks
     */
    public function chunk(string $text, int $chunkSize = 500, int $overlap = 50): array
    {
        $words = preg_split('/\s+/', trim($text));
        $chunks = [];

        for ($i = 0; $i < count($words); $i += ($chunkSize - $overlap)) {
            $chunks[] = implode(' ', array_slice($words, $i, $chunkSize));
        }

        return $chunks;
    }
}
