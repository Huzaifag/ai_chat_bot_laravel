@extends('admin.layouts.app')

@section('pageTitle', 'System Settings')

@push('styles')
<style>
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .tab-button.active {
        background-color: #3B82F6;
        color: white;
    }
</style>
@endpush

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Settings</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your application settings and configurations</p>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg dark:bg-green-900 dark:border-green-700 dark:text-green-200">
        {{ session('success') }}
    </div>
    @endif

    <!-- Error Messages -->
    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg dark:bg-red-900 dark:border-red-700 dark:text-red-200">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Settings Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex flex-wrap -mb-px" aria-label="Tabs">
                <button type="button" class="tab-button active px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 dark:text-gray-300 dark:hover:border-gray-600" data-tab="general">
                    General
                </button>
                <button type="button" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 dark:text-gray-300 dark:hover:border-gray-600" data-tab="ai_chat">
                    AI & Chat
                </button>
                <button type="button" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 dark:text-gray-300 dark:hover:border-gray-600" data-tab="documents">
                    Documents
                </button>
                <button type="button" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 dark:text-gray-300 dark:hover:border-gray-600" data-tab="appearance">
                    Appearance
                </button>
                <button type="button" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent hover:border-gray-300 dark:text-gray-300 dark:hover:border-gray-600" data-tab="security">
                    Security
                </button>
            </nav>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('POST')

            <!-- Tab Contents -->
            <div class="p-6">
                @foreach($settings as $group => $groupSettings)
                <div id="{{ $group }}" class="tab-content {{ $loop->first ? 'active' : '' }}">
                    <div class="space-y-6">
                        @foreach($groupSettings as $setting)
                            <div class="setting-field">
                                <label for="{{ $setting['key'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ $setting['label'] }}
                                        </label>

                                        @if($setting['type'] === 'text')
                                <input 
                                    type="text" 
                                    name="{{ $setting['key'] }}" 
                                    id="{{ $setting['key'] }}" 
                                    value="{{ old($setting['key'], $setting['value']) }}"
                                    placeholder="{{ $setting['placeholder'] ?? '' }}"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >

                            @elseif($setting['type'] === 'textarea')
                                <textarea 
                                    name="{{ $setting['key'] }}" 
                                    id="{{ $setting['key'] }}" 
                                    rows="4"
                                    placeholder="{{ $setting['placeholder'] ?? '' }}"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >{{ old($setting['key'], $setting['value']) }}</textarea>

                            @elseif($setting['type'] === 'number')
                                <input 
                                    type="number" 
                                    name="{{ $setting['key'] }}" 
                                    id="{{ $setting['key'] }}" 
                                    value="{{ old($setting['key'], $setting['value']) }}"
                                    placeholder="{{ $setting['placeholder'] ?? '' }}"
                                    step="{{ $setting['step'] ?? '1' }}"
                                    min="{{ $setting['min'] ?? '' }}"
                                    max="{{ $setting['max'] ?? '' }}"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >

                            @elseif($setting['type'] === 'boolean')
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        name="{{ $setting['key'] }}" 
                                        id="{{ $setting['key'] }}" 
                                        value="1"
                                        {{ old($setting['key'], $setting['value']) == '1' ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    >
                                    <label for="{{ $setting['key'] }}" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                        Enable this option
                                    </label>
                                </div>

                            @elseif($setting['type'] === 'image')
                                <div class="space-y-3">
                                    @if($setting['value'])
                                        <div class="mb-3">
                                            <img 
                                                src="{{ asset('storage/' . $setting['value']) }}" 
                                                alt="{{ $setting['label'] }}"
                                                class="h-20 w-auto object-contain border border-gray-200 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-700"
                                            >
                                        </div>
                                    @endif
                                    <input 
                                        type="file" 
                                        name="{{ $setting['key'] }}" 
                                        id="{{ $setting['key'] }}" 
                                        accept="image/*"
                                        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-200"
                                    >
                                </div>

                            @elseif($setting['type'] === 'select')
                                <select 
                                    name="{{ $setting['key'] }}" 
                                    id="{{ $setting['key'] }}"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    @foreach($setting['options'] as $optionValue => $optionLabel)
                                        <option 
                                            value="{{ $optionValue }}" 
                                            {{ old($setting['key'], $setting['value']) == $optionValue ? 'selected' : '' }}
                                        >
                                            {{ $optionLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @if(isset($setting['help']))
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $setting['help'] }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 rounded-b-lg flex justify-end space-x-3">
                <button 
                    type="button" 
                    onclick="window.location.reload()"
                    class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium"
                >
                    Reset
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
                >
                    Save Settings
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        });
    });
</script>
@endpush
