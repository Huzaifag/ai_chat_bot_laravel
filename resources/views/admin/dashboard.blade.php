@extends('admin.layouts.app')

@section('pageTitle', 'Dashboard Overview')

@section('content')
    <!-- Stats Grid -->
    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">

        <x-admin.stats-card
            icon="file-text"
            color="blue"
            title="Total Documents"
            :value="$totalDocs ?? 0"
            subtitle="Documents Uploaded"
        />

        <x-admin.stats-card
            icon="database"
            color="purple"
            title="Total Embeddings"
            :value="$totalEmbeddings ?? 0"
            subtitle="Vector Embeddings"
        />

        <x-admin.stats-card
            icon="message-circle"
            color="green"
            title="Total Chats"
            :value="$analytics['totalChats'] ?? 0"
            subtitle="User Conversations"
        />

        <x-admin.stats-card
            icon="zap"
            color="yellow"
            title="Avg Response"
            :value="($analytics['avgResponseTime'] ?? 0) . 'ms'"
            subtitle="Response Time"
        />

        <x-admin.stats-card
            icon="activity"
            color="green"
            title="Operational"
            value="Active"
            subtitle="System Status"
        >
            <span class="h-2 w-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)] animate-pulse"></span>
        </x-admin.stats-card>
        </div>
    </div>

    <!-- Analytics Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- API Usage by Provider -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="cpu" class="w-5 h-5 text-blue-500"></i>
                    API Usage by Provider
                </h3>
            </div>
            <div class="relative h-64">
                <canvas id="apiProviderChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4">
                @foreach($analytics['apiUsageByProvider'] ?? [] as $provider)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ $provider->api_provider ?? 'Unknown' }}</p>
                        <p class="text-xl font-bold text-gray-800 dark:text-white">{{ number_format($provider->count ?? 0) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ number_format($provider->total_tokens ?? 0) }} tokens
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- API Usage Last 7 Days -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>
                    API Usage (Last 7 Days)
                </h3>
            </div>
            <div class="relative h-64">
                <canvas id="apiUsageTrendChart"></canvas>
            </div>
        </div>

        <!-- Top Documents -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-purple-500"></i>
                    Top Documents by Interactions
                </h3>
            </div>
            <div class="relative h-64">
                <canvas id="topDocumentsChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity (30 Days) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-orange-500"></i>
                    Chat Activity (30 Days)
                </h3>
            </div>
            <div class="relative h-64">
                <canvas id="recentActivityChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Recent Documents Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="font-semibold text-gray-800 dark:text-white">Recent Documents</h3>
            <x-admin.button variant="secondary" size="sm" :href="route('admin.documents.index')">
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
        // Analytics data from Laravel
        const analyticsData = @json($analytics);

        // Dashboard specific JS
        document.addEventListener('DOMContentLoaded', async () => {
            await updateDashboardStats();
            initializeCharts();
            
            const clearBtn = document.getElementById('clear-cache-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', async function() {
                    this.disabled = true;
                    this.textContent = 'Clearing...';
                    const msgBox = document.getElementById('cache-message');
                    if (msgBox) msgBox.classList.add('hidden');
                    try {
                        const res = await fetch('/optimize');
                        const msg = await res.text();
                        if (msgBox) {
                            msgBox.textContent = msg;
                            msgBox.className = 'block px-4 py-3 rounded-lg text-sm font-medium mt-2 bg-green-50 text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800';
                        }
                    } catch (e) {
                        if (msgBox) {
                            msgBox.textContent = 'Failed to clear cache.';
                            msgBox.className = 'block px-4 py-3 rounded-lg text-sm font-medium mt-2 bg-red-50 text-red-800 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800';
                        }
                    }
                    if (msgBox) msgBox.classList.remove('hidden');
                    this.disabled = false;
                    this.innerHTML = '<i data-lucide="refresh-ccw" class="w-4 h-4"></i> Clear All Cache';
                    lucide.createIcons();
                });
            }
        });

        function initializeCharts() {
            // Chart.js default colors
            const chartColors = {
                gemini: 'rgba(59, 130, 246, 0.8)', // blue
                openai: 'rgba(16, 185, 129, 0.8)', // green
                other: 'rgba(168, 85, 247, 0.8)', // purple
            };

            // 1. API Usage by Provider - Doughnut Chart
            const apiProviderCtx = document.getElementById('apiProviderChart');
            if (apiProviderCtx) {
                const providerData = analyticsData.apiUsageByProvider || [];
                new Chart(apiProviderCtx, {
                    type: 'doughnut',
                    data: {
                        labels: providerData.map(p => (p.api_provider || 'Unknown').toUpperCase()),
                        datasets: [{
                            data: providerData.map(p => p.count || 0),
                            backgroundColor: providerData.map(p => chartColors[p.api_provider] || chartColors.other),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: { size: 12 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const provider = providerData[context.dataIndex];
                                        return [
                                            `Calls: ${context.parsed}`,
                                            `Tokens: ${(provider.total_tokens || 0).toLocaleString()}`,
                                            `Cost: $${(provider.total_cost || 0).toFixed(4)}`
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. API Usage Trend (Last 7 Days) - Line Chart
            const apiTrendCtx = document.getElementById('apiUsageTrendChart');
            if (apiTrendCtx) {
                const trendData = analyticsData.last7Days || [];
                const dates = [...new Set(trendData.map(d => d.date))].sort();
                const providers = [...new Set(trendData.map(d => d.api_provider))];
                
                const datasets = providers.map(provider => {
                    return {
                        label: (provider || 'Unknown').toUpperCase(),
                        data: dates.map(date => {
                            const entry = trendData.find(d => d.date === date && d.api_provider === provider);
                            return entry ? entry.count : 0;
                        }),
                        borderColor: chartColors[provider] || chartColors.other,
                        backgroundColor: (chartColors[provider] || chartColors.other).replace('0.8', '0.1'),
                        tension: 0.4,
                        fill: true
                    };
                });

                new Chart(apiTrendCtx, {
                    type: 'line',
                    data: {
                        labels: dates.map(d => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: { size: 12 }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // 3. Top Documents - Horizontal Bar Chart
            const topDocsCtx = document.getElementById('topDocumentsChart');
            if (topDocsCtx) {
                const topDocs = analyticsData.topDocuments || [];
                new Chart(topDocsCtx, {
                    type: 'bar',
                    data: {
                        labels: topDocs.map(d => d.file_name || 'Unknown'),
                        datasets: [{
                            label: 'Interactions',
                            data: topDocs.map(d => d.chats_count || 0),
                            backgroundColor: 'rgba(168, 85, 247, 0.8)',
                            borderColor: 'rgba(168, 85, 247, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // 4. Recent Activity (30 Days) - Area Chart
            const activityCtx = document.getElementById('recentActivityChart');
            if (activityCtx) {
                const activity = analyticsData.recentActivity || [];
                new Chart(activityCtx, {
                    type: 'line',
                    data: {
                        labels: activity.map(a => new Date(a.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                        datasets: [{
                            label: 'Chat Sessions',
                            data: activity.map(a => a.count || 0),
                            borderColor: 'rgba(249, 115, 22, 0.8)',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>
    @endpush
@endsection