@extends('admin.layouts.app')

@section('pageTitle', 'Dashboard Overview')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-admin.stats-card
            icon="file-text"
            color="blue"
            title="Total Documents"
            :value="$totalDocs ?? 0"
            subtitle="Total Documents"
            badge="+12%"
        />

        <x-admin.stats-card
            icon="database"
            color="purple"
            title="Total Embeddings"
            :value="$totalEmbeddings ?? 0"
            subtitle="Total Embeddings"
            badge="+5%"
        />

        <x-admin.stats-card
            icon="clock"
            color="orange"
            title="Last Upload"
            :value="$lastUpload ?? 'None'"
            subtitle="Last Upload"
        />

        <x-admin.stats-card
            icon="activity"
            color="green"
            title="Operational"
            value="Operational"
            subtitle="System Status"
        >
            <span class="h-2 w-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)] animate-pulse"></span>
        </x-admin.stats-card>
    </div>

    <!-- Recent Activity Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-semibold text-gray-800 dark:text-white">Recent Documents</h3>
            <x-admin.button variant="secondary" size="sm" onclick="router('documents')">
                View All
            </x-admin.button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">File Name</th>
                        <th class="px-6 py-4">Uploaded</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody id="recent-docs-body" class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        // Dashboard specific JS
        document.addEventListener('DOMContentLoaded', async () => {
            await updateDashboardStats();
        });
    </script>
    @endpush
@endsection