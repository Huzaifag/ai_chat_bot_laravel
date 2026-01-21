<?php

namespace App\Services;

use Exception;
use App\Models\Document;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordReader;

class DocumentTextExtractor
{
    /**
     * Extract text from a Document model.
     */
    public function extract(Document $document): string
    {
        $path = storage_path('app/private/' . $document->stored_path);

        return match ($document->mime_type) {
            'application/pdf' => $this->extractPdf($path),
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => $this->extractDocx($path),
            'text/plain', 'text/csv' => file_get_contents($path),
            default => throw new Exception("Unsupported file type: {$document->mime_type}"),
        };
    }

    protected function extractPdf(string $path): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }

    protected function extractDocx(string $path): string
    {
        $phpWord = WordReader::load($path);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }
        return $text;
    }
}
