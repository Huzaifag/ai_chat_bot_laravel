@extends('admin.layouts.app')

@section('pageTitle', 'AI API Configurations')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI API Configurations</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Manage API keys and settings for AI providers</p>
                </div>
                <button onclick="showForm()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4 mr-2 inline"></i>
                    Add Configuration
                </button>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 mr-2"></i>
                    <p class="text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Configurations Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-4">Provider</th>
                            <th class="px-6 py-4">Model</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Created</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                        @forelse($configs as $config)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ ucfirst($config->provider) }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    {{ $config->version ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($config->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                            <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                    {{ $config->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="editConfig({{ $config->id }}, '{{ $config->provider }}','{{ $config->api_key }}', '{{ $config->version }}', {{ $config->is_active ? 'true' : 'false' }})" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded dark:hover:bg-gray-700 dark:hover:text-blue-400" title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.ai-api-configs.destroy', $config->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this configuration?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded dark:hover:bg-gray-700 dark:hover:text-red-400" title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i data-lucide="settings" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                                    <p class="text-lg font-medium mb-2">No API configurations found</p>
                                    <p class="text-sm">Add your first AI provider configuration to get started.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Configuration Form Modal -->
        <div id="config-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modal-title">Add API Configuration</h3>
                            <button onclick="hideForm()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <form id="config-form" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provider</label>
                                <select name="provider" id="provider" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                                    <option value="">Select Provider</option>
                                    <option value="gemini">Gemini</option>
                                    <option value="openai">OpenAI</option>
                                    <option value="anthropic">Anthropic</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                                <input type="password" name="api_key" id="api_key" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Your API key will be encrypted and stored securely.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model </label>
                                <input type="text" name="version" id="version" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="e.g., v1, 1.0, latest">
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded">
                                <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Active Configuration</label>
                            </div>

                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" onclick="hideForm()" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                    Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showForm(configId = null, provider = '', apiKey = '', version = '', isActive = false) {
            const modal = document.getElementById('config-modal');
            const form = document.getElementById('config-form');
            const title = document.getElementById('modal-title');

            if (configId) {
                title.textContent = 'Edit API Configuration';
                form.action = `/admin/ai-api-configs/${configId}`;
                form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');
            } else {
                title.textContent = 'Add API Configuration';
                form.action = '/admin/ai-api-configs';
            }

            document.getElementById('provider').value = provider;
            document.getElementById('api_key').value = apiKey || '';
            document.getElementById('version').value = version || '';
            document.getElementById('is_active').checked = isActive;
            

            modal.classList.remove('hidden');
        }

        function editConfig(id, provider, apiKey, version, isActive) {
            showForm(id, provider, apiKey, version, isActive);
        }

        function hideForm() {
            const modal = document.getElementById('config-modal');
            const form = document.getElementById('config-form');
            const methodInput = form.querySelector('input[name="_method"]');

            modal.classList.add('hidden');
            form.reset();
            if (methodInput) methodInput.remove();
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @endpush
@endsection