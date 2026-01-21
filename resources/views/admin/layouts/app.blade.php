<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'KnowledgeBase Admin | Chatbot Management' }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons (Lucide via UNPKG for simplicity in single file) -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Drag & Drop Zone Animation */
        .drag-active {
            border-color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.05);
        }

        /* Fade In Animation */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 font-sans antialiased transition-colors duration-200">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="hidden md:flex flex-col w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-colors duration-200 z-20">
            <div class="flex items-center justify-center h-16 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-primary-600 rounded-lg">
                        <i data-lucide="bot" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">BotAdmin</span>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" id="nav-dashboard" class="nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-primary-50 text-primary-700 dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="{{ route('admin.documents.index') }}" id="nav-documents" class="nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.documents.index') ? 'bg-primary-50 text-primary-700 dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <i data-lucide="file-text" class="w-5 h-5 mr-3"></i>
                    Documents
                </a>
                <a href="{{ route('admin.upload') }}" id="nav-upload" class="nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.upload') ? 'bg-primary-50 text-primary-700 dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <i data-lucide="upload-cloud" class="w-5 h-5 mr-3"></i>
                    Upload Data
                </a>
                <a href="{{ route('admin.chatlogs') }}" id="nav-chatlogs" class="nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.chatlogs') ? 'bg-primary-50 text-primary-700 dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <i data-lucide="message-square" class="w-5 h-5 mr-3"></i>
                    Chat Logs
                </a>
                <a href="{{ route('admin.ai-api-configs.index') }}" id="nav-ai-configs" class="nav-item w-full flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('admin.ai-api-configs*') ? 'bg-primary-50 text-primary-700 dark:bg-gray-700 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <i data-lucide="settings" class="w-5 h-5 mr-3"></i>
                    AI API Configs
                </a>
            </nav>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <img class="h-9 w-9 rounded-full ring-2 ring-white dark:ring-gray-700" src="https://ui-avatars.com/api/?name=Admin+User&background=0D8ABC&color=fff" alt="Admin">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">Admin User</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">admin@botadmin.ai</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-full overflow-hidden relative">

            <!-- Header -->
            <header class="h-16 flex items-center justify-between px-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-200 z-10">
                <div class="flex items-center gap-4">
                    <button class="md:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <h2 id="page-title" class="text-lg font-semibold text-gray-800 dark:text-white">@yield('pageTitle', 'Overview')</h2>
                </div>

                <div class="flex items-center gap-4">
                    <button id="theme-toggle" class="p-2 rounded-full text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                        <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 rounded-full text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors" title="Logout">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                    <button class="relative p-2 rounded-full text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-800"></span>
                    </button>
                </div>
            </header>

            <!-- Scrollable Main Area -->
            <main class="flex-1 overflow-y-auto p-6 scroll-smooth">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-5 right-5 transform translate-y-20 opacity-0 transition-all duration-300 z-50">
        <div class="bg-white dark:bg-gray-800 border-l-4 border-green-500 rounded shadow-lg p-4 flex items-center gap-3 pr-8">
            <i data-lucide="check-circle" class="text-green-500 w-5 h-5"></i>
            <div>
                <p class="font-bold text-gray-900 dark:text-white text-sm">Success</p>
                <p class="text-sm text-gray-600 dark:text-gray-300" id="toast-message">Operation completed.</p>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>