@extends('admin.layouts.app')

@section('pageTitle', 'Read Document')

@section('content')
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.documents.index') }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600 dark:text-gray-400"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $document->original_name }}</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Uploaded {{ $document->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.documents.download', $document->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                        Download
                    </a>
                </div>
            </div>

            <!-- Document Info -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">File Type</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $document->mime_type }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">File Size</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ number_format($document->size / 1024 / 1024, 2) }} MB</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</p>
                    <p class="text-sm font-medium mt-1">
                        @if($document->status === 'processed')
                            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-xs">Processed</span>
                        @elseif($document->status === 'processing')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-xs">Processing</span>
                        @elseif($document->status === 'failed')
                            <span class="px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full text-xs">Failed</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 rounded-full text-xs">{{ ucfirst($document->status) }}</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Chunks</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $document->chunks->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
            @if($document->status === 'processed' && !empty($content))
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    <div class="whitespace-pre-wrap text-gray-700 dark:text-gray-300 leading-relaxed">{{ $content }}</div>
                </div>
            @elseif($document->status === 'processing')
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full mb-4">
                        <i data-lucide="loader" class="w-8 h-8 text-blue-600 dark:text-blue-400 animate-spin"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">Document is being processed...</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">Please check back in a few moments.</p>
                </div>
            @elseif($document->status === 'failed')
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                        <i data-lucide="alert-circle" class="w-8 h-8 text-red-600 dark:text-red-400"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">Document processing failed</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">The content could not be extracted from this file.</p>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                        <i data-lucide="file-text" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">No content available</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">This document has not been processed yet.</p>
                </div>
            @endif
        </div>

        <!-- Chunks Preview (Optional) -->
        @if($document->status === 'processed' && $document->chunks->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mt-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Document Chunks ({{ $document->chunks->count() }})</h2>
            <div class="space-y-3">
                @foreach($document->chunks->take(5) as $chunk)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Chunk #{{ $chunk->chunk_index + 1 }}</span>
                            <span class="text-xs text-gray-400">{{ strlen($chunk->chunk_text) }} characters</span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">{{ $chunk->chunk_text }}</p>
                    </div>
                @endforeach
                @if($document->chunks->count() > 5)
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                        ... and {{ $document->chunks->count() - 5 }} more chunks
                    </p>
                @endif
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        lucide.createIcons();
    </script>
    @endpush
@endsection
