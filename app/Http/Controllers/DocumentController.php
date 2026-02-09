<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentJob;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\DocumentTextExtractor;
use App\Services\TextChunker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    protected DocumentTextExtractor $textExtractor;
    protected TextChunker $textChunker;

    public function __construct(DocumentTextExtractor $textExtractor, TextChunker $textChunker)
    {
        $this->textExtractor = $textExtractor;
        $this->textChunker = $textChunker;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Document::where('uploaded_by', auth('admin')->id());

        if ($request->has('slug') && $request->slug) {
            $query->where('slug', $request->slug);
        }

        $documents = $query->withCount('embeddings')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $slugs = Document::where('uploaded_by', auth('admin')->id())
            ->whereNotNull('slug')
            ->distinct()
            ->pluck('slug')
            ->sort();

        if ($request->wantsJson()) {
            return response()->json($documents);
        }

        return view('admin.documents', compact('documents', 'slugs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get settings
        $maxSizeMB = (int) system_setting('max_document_size_mb', '10');
        $allowedTypes = system_setting('allowed_document_types', 'pdf,docx,txt,csv');

        // Prepare validation rules
        $allowedMimes = str_replace(',', ',', $allowedTypes);
        $maxSizeKB = $maxSizeMB * 1024;

        $request->validate([
            'document' => 'required|array',
            'document.*' => 'file|mimes:' . $allowedMimes . '|max:' . $maxSizeKB,
            'slug' => 'nullable|string|max:255',
        ]);

        $uploadedDocuments = [];

        \DB::transaction(function () use ($request, &$uploadedDocuments) {
            foreach ($request->file('document') as $file) {
                $path = $file->store('documents', 'private');

                $document = Document::create([
                    'original_name' => $file->getClientOriginalName(),
                    'stored_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'status' => 'uploaded',
                    'slug' => $request->slug,
                    'uploaded_by' => auth('admin')->id(),
                ]);

                $uploadedDocuments[] = $document;
            }
        });

        // Dispatch processing jobs based on mode
        $processingMode = system_setting('document_processing_mode', 'full');
        
        foreach ($uploadedDocuments as $document) {
            // Always queue for background processing
            ProcessDocumentJob::dispatch($document->id);
        }

        return response()->json([
            'message' => 'Documents uploaded and processing started',
            'documents' => $uploadedDocuments
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $document = Document::where('id', $id)->where('uploaded_by', auth('admin')->id())->with('chunks')->firstOrFail();

        return view('admin.document-show', compact('document'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Download the document file.
     */
    public function download(string $id)
    {
        $document = Document::where('id', $id)->where('uploaded_by', auth('admin')->id())->firstOrFail();

        if (!Storage::disk('private')->exists($document->stored_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('private')->download($document->stored_path, $document->original_name);
    }

    /**
     * Read/view the document content.
     */
    public function read(string $id)
    {
        $document = Document::where('id', $id)
            ->where('uploaded_by', auth('admin')->id())
            ->with('chunks')
            ->firstOrFail();

        // Get extracted text content
        $content = $document->chunks->sortBy('chunk_index')->pluck('chunk_text')->implode("\n\n");

        return view('admin.document-read', compact('document', 'content'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $document = Document::where('id', $id)->where('uploaded_by', auth('admin')->id())->firstOrFail();

        // Delete the file from storage
        Storage::disk('private')->delete($document->stored_path);

        // Delete related chunks and embeddings (cascade should handle)
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }
}
