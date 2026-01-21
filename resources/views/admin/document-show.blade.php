@extends('admin.layouts.app')

@section('pageTitle', 'Document Details: ' . $document->original_name)

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Document Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $document->original_name }}</h1>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ $document->mime_type }} • {{ number_format($document->size / 1024, 1) }} KB •
                            Uploaded {{ $document->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $statusStyles = [
                            'Ready' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'Processing' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'Failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                        ];
                        $statusIcons = [
                            'Ready' => 'check-circle',
                            'Processing' => 'loader-2',
                            'Failed' => 'alert-circle'
                        ];
                        $iconClass = $document->status === 'Processing' ? 'animate-spin' : '';
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusStyles[$document->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' }}">
                        <i data-lucide="{{ $statusIcons[$document->status] ?? 'file' }}" class="w-3 h-3 mr-1 {{ $iconClass }}"></i>
                        {{ $document->status }}
                    </span>
                    <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2 inline"></i>
                        Back to Documents
                    </a>
                </div>
            </div>
        </div>

        <!-- Document Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg">
                        <i data-lucide="database" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $document->chunks->count() }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Text Chunks</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg">
                        <i data-lucide="hash" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $document->chunks->sum(fn($chunk) => str_word_count($chunk->chunk_text)) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Words</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-lg">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $document->updated_at->diffForHumans() }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Last Processed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chunks Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Document Chunks</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Text segments extracted from the document for processing</p>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($document->chunks->sortBy('chunk_index') as $chunk)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium rounded">
                                        Chunk #{{ $chunk->chunk_index + 1 }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ str_word_count($chunk->chunk_text) }} words
                                    </span>
                                </div>
                                <div class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                    {{ Str::limit($chunk->chunk_text, 300) }}
                                    @if(strlen($chunk->chunk_text) > 300)
                                        <button class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium ml-1" onclick="toggleChunk(this)">
                                            Show more
                                        </button>
                                    @endif
                                </div>
                                <div class="hidden text-gray-700 dark:text-gray-300 leading-relaxed mt-2">
                                    {{ $chunk->chunk_text }}
                                    <button class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium ml-1" onclick="toggleChunk(this)">
                                        Show less
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Copy chunk text" onclick="copyToClipboard('{{ addslashes($chunk->chunk_text) }}')">
                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <i data-lucide="file-x" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No chunks available</h3>
                        <p class="text-gray-500 dark:text-gray-400">This document hasn't been processed yet or processing failed.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleChunk(button) {
            const container = button.closest('.flex-1');
            const preview = container.querySelector('.leading-relaxed:not(.hidden)');
            const full = container.querySelector('.leading-relaxed.hidden');

            if (preview && full) {
                preview.classList.add('hidden');
                full.classList.remove('hidden');
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Chunk text copied to clipboard');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('Chunk text copied to clipboard');
            });
        }

        function showToast(message) {
            const toast = document.getElementById('toast');
            if (!toast) return;

            const toastMsg = document.getElementById('toast-message');
            toastMsg.innerText = message;

            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
                toast.classList.remove('translate-y-0', 'opacity-100');
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @endpush
@endsection