@extends('admin.layouts.app')

@section('pageTitle', 'Document Management')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <!-- Toolbar -->
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex gap-4">
                <div class="relative w-full sm:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                    </div>
                    <input type="text" id="document-search" placeholder="Search documents..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition duration-150 ease-in-out">
                </div>
                <div class="relative">
                    <select id="interest-filter" class="block w-full pl-3 pr-8 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition duration-150 ease-in-out">
                        <option value="">All Interests</option>
                        @php
                            $predefinedSlugs = ['general', 'technical', 'business', 'medical', 'legal', 'educational', 'research'];
                            $allSlugs = collect($predefinedSlugs)->merge($slugs)->unique()->sort();
                        @endphp
                        @foreach($allSlugs as $slug)
                            <option value="{{ $slug }}" {{ request('slug') == $slug ? 'selected' : '' }}>{{ ucfirst($slug) }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                        {{-- <i data-lucide="chevron-down" class="h-4 w-4 text-gray-400"></i> --}}
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <x-admin.button onclick="router('upload')">
                    <i data-lucide="plus" class="h-4 w-4 mr-2"></i>
                    Add Document
                </x-admin.button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto min-h-[400px]">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">File Name</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Size</th>
                        <th class="px-6 py-4">Uploaded At</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Embeddings</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="documents-table-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm text-gray-700 dark:text-gray-300">
                    <!-- Rows rendered by JS -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <span class="text-sm text-gray-500 dark:text-gray-400">Showing <span class="font-medium text-gray-900 dark:text-white" id="pagination-info">1-10</span> of <span id="total-items">0</span></span>
            <div class="flex gap-2">
                <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-colors" disabled>Previous</button>
                <button class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition-colors">Next</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Documents specific JS
        document.addEventListener('DOMContentLoaded', async () => {
            await renderDocumentsTable();

            // Setup search
            const searchInput = document.getElementById('document-search');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    renderDocumentsTable(e.target.value);
                });
            }

            // Setup interest filter
            const interestFilter = document.getElementById('interest-filter');
            if (interestFilter) {
                interestFilter.addEventListener('change', (e) => {
                    const url = new URL(window.location);
                    if (e.target.value) {
                        url.searchParams.set('slug', e.target.value);
                    } else {
                        url.searchParams.delete('slug');
                    }
                    window.location.href = url.toString();
                });
            }
        });
    </script>
    @endpush
@endsection