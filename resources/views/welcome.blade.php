<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Chatbot UI</title>

       <!-- Favicon -->
    @if(system_setting('app_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . system_setting('app_favicon')) }}">
    @endif
    
    <!-- Fonts: Inter (Premium Standard) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media', // Uses system preference
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb', // Primary Brand Color
                            700: '#1d4ed8',
                        }
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.3s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px) scale(0.95)' },
                            '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Custom Scrollbar for Webkit */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.3);
            border-radius: 20px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(75, 85, 99, 0.4);
        }

        /* Typing Indicator Bounce */
        .typing-dot {
            animation: bounce 1.4s infinite ease-in-out both;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 h-screen w-full flex items-center justify-center sm:p-6 transition-colors duration-300">

    <!-- Mode Selector Modal -->
    <div id="mode-selector" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" style="display:none;">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 max-w-xs w-full text-center">
            <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Choose Assistant Mode</h2>
            <p class="text-sm text-gray-500 dark:text-gray-300 mb-6">Select how you want the assistant to respond:</p>
            <button class="mode-btn w-full mb-3 px-4 py-2 rounded-lg bg-brand-600 text-white font-semibold hover:bg-brand-700 transition" data-mode="default">Standard Mode</button>
            <button class="mode-btn w-full px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition" data-mode="research">Research Mode</button>
        </div>
    </div>

       

    <!-- Main App Window -->
    <!-- On mobile: w-full h-full (full screen) -->
    <!-- On desktop: max-width container with rounded corners and shadow (app-like feel) -->
    <div id="chat-window" class="w-full h-full sm:max-w-[480px] md:max-w-[700px] lg:max-w-[900px] sm:h-[85vh] bg-white dark:bg-gray-800 sm:rounded-2xl sm:shadow-2xl border-0 sm:border border-gray-100 dark:border-gray-700 flex flex-col overflow-hidden relative">
        
        <!-- Header -->
        <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    @if(system_setting('app_favicon'))
                        <img src="{{ asset('storage/' . system_setting('app_favicon')) }}" alt="{{ system_setting('app_name', 'Logo') }}" class="w-10 h-10 rounded-full object-cover shadow-md ring-2 ring-white dark:ring-gray-800">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-brand-500 to-indigo-600 flex items-center justify-center text-white shadow-md ring-2 ring-white dark:ring-gray-800">
                            <i data-lucide="bot" class="w-6 h-6"></i>
                        </div>
                    @endif
                </div>
                <div>
                    <span class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">{{ system_setting('app_name', 'Premium Chatbot') }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ system_setting('app_tagline', 'Your AI Assistant') }}</span>
                </div>
            </div>
            
            <!-- Header Actions -->
            <div class="flex items-center space-x-2">
                <!-- Interest Selector Dropdown -->
                <div class="relative">
                    <button id="interest-toggle" class="p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors" title="Select Interest">
                        <i data-lucide="filter" class="w-5 h-5"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="interest-dropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 py-1 z-20 hidden">
                        <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Select Interest</p>
                        </div>
                        <button onclick="selectInterest('')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span>All Interests</span>
                            <i data-lucide="check" class="w-4 h-4 text-brand-600" id="check-all" style="display: none;"></i>
                        </button>
                        <button onclick="selectInterest('general')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span>General</span>
                            <i data-lucide="check" class="w-4 h-4 text-brand-600" id="check-general" style="display: none;"></i>
                        </button>
                        <button onclick="selectInterest('technical')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span>Technical</span>
                            <i data-lucide="check" class="w-4 h-4 text-brand-600" id="check-technical" style="display: none;"></i>
                        </button>
                        <button onclick="selectInterest('business')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span>Business</span>
                            <i data-lucide="check" class="w-4 h-4 text-brand-600" id="check-business" style="display: none;"></i>
                        </button>
                        <button onclick="selectInterest('medical')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span>Medical</span>
                            <i data-lucide="check" class="w-4 h-4 text-brand-600" id="check-medical" style="display: none;"></i>
                        </button>
                        <button onclick="selectInterest('legal')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span>Legal</span>
                            <i data-lucide="check" class="w-4 h-4 text-brand-600" id="check-legal" style="display: none;"></i>
                        </button>
                        <button onclick="selectInterest('educational')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span>Educational</span>
                            <i data-lucide="check" class="w-4 h-4 text-brand-600" id="check-educational" style="display: none;"></i>
                        </button>
                        <button onclick="selectInterest('research')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between">
                            <span>Research</span>
                            <i data-lucide="check" class="w-4 h-4 text-brand-600" id="check-research" style="display: none;"></i>
                        </button>
                    </div>
                </div>
                
                @auth('admin')
                    <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors" title="Admin Panel" target="_blank">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Messages Area -->
        <div id="messages-container" class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 space-y-6 bg-gray-50/50 dark:bg-gray-900/50">
            
            <!-- Date Separator -->
            <div class="flex justify-center my-4">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">Today</span>
            </div>

            <!-- Bot Welcome Message (Static) -->
            <div class="flex items-start space-x-3 message-enter max-w-3xl mx-auto w-full">
                <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex-shrink-0 flex items-center justify-center text-brand-600 dark:text-brand-400 mt-1">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </div>
                <div class="space-y-1 flex-1">
                    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl rounded-tl-none px-5 py-4 shadow-sm w-fit max-w-[90%] sm:max-w-[80%]">
                        <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                            Hello! 👋 I'm your dedicated virtual assistant. <br><br>
                            I can help you analyze documents, manage workflows, or answer complex support questions. How can I help you today?
                        </p>
                    </div>
                    <span class="text-[10px] text-gray-400 ml-1">Just now</span>
                </div>
            </div>
        </div>

        <!-- Typing Indicator (Hidden by default) -->
        <div id="typing-indicator" class="hidden px-6 pb-2 pt-0 bg-gray-50/50 dark:bg-gray-900/50">
            <div class="max-w-3xl mx-auto w-full flex items-start space-x-3">
                <div class="w-8 h-8 flex-shrink-0"></div> <!-- Spacer alignment -->
                <div class="bg-gray-200 dark:bg-gray-700 rounded-2xl rounded-tl-none px-4 py-3 w-16 flex items-center justify-center space-x-1">
                    <div class="w-1.5 h-1.5 bg-gray-500 dark:bg-gray-400 rounded-full typing-dot"></div>
                    <div class="w-1.5 h-1.5 bg-gray-500 dark:bg-gray-400 rounded-full typing-dot"></div>
                    <div class="w-1.5 h-1.5 bg-gray-500 dark:bg-gray-400 rounded-full typing-dot"></div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
            <div class="max-w-3xl mx-auto w-full">
                <form id="chat-form" class="relative flex items-end shadow-sm rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 focus-within:ring-2 focus-within:ring-brand-500/20 focus-within:border-brand-500 transition-all">
                    <input 
                        type="text" 
                        id="chat-input" 
                        placeholder="Ask anything about your documents..." 
                        class="w-full pl-4 pr-14 py-4 bg-transparent border-none text-sm focus:ring-0 text-gray-900 dark:text-white placeholder-gray-400"
                        autocomplete="off"
                    >
                    <button 
                        type="submit" 
                        id="send-btn"
                        class="absolute right-2 bottom-2 p-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-300 dark:disabled:bg-gray-700 transform active:scale-95 flex items-center justify-center"
                        disabled
                    >
                        <i data-lucide="arrow-up" class="w-5 h-5"></i>
                    </button>
                </form>
                <div class="text-center mt-3 flex items-center justify-center space-x-2">
                    <i data-lucide="lock" class="w-3 h-3 text-gray-400"></i>
                    <p class="text-[10px] text-gray-400">Secure Enterprise Environment • <a href="#" class="hover:underline hover:text-brand-500 transition-colors">Privacy Policy</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize Icons
            lucide.createIcons();

            // State
            let isThinking = false;

            // Elements
            const $input = $('#chat-input');
            const $sendBtn = $('#send-btn');
            const $messages = $('#messages-container');
            const $form = $('#chat-form');
            const $typingIndicator = $('#typing-indicator');

            // --- UI Helper Functions ---

            function scrollToBottom() {
                $messages.animate({ scrollTop: $messages.prop("scrollHeight") }, 400);
            }

            function formatTime() {
                const now = new Date();
                return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            function appendUserMessage(text) {
                const html = `
                    <div class="flex items-end justify-end space-x-2 animate-fade-in-up max-w-3xl mx-auto w-full">
                        <div class="space-y-1 max-w-[90%] sm:max-w-[80%] flex flex-col items-end ml-auto">
                            <div class="bg-brand-600 text-white rounded-2xl rounded-tr-none px-5 py-3 shadow-md">
                                <p class="text-sm leading-relaxed">${escapeHtml(text)}</p>
                            </div>
                            <span class="text-[10px] text-gray-400 mr-1">${formatTime()}</span>
                        </div>
                    </div>
                `;
                $messages.append(html);
                scrollToBottom();
            }

            function appendBotMessage(text) {
                const html = `
                    <div class="flex items-start space-x-3 animate-fade-in-up max-w-3xl mx-auto w-full">
                        <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex-shrink-0 flex items-center justify-center text-brand-600 dark:text-brand-400 mt-1">
                            <i data-lucide="bot" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-1 flex-1">
                            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl rounded-tl-none px-5 py-3 shadow-sm w-fit max-w-[90%] sm:max-w-[80%] prose dark:prose-invert max-w-none">
                                <span class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                                    ${window.renderMarkdown ? window.renderMarkdown(text) : text}
                                </span>
                            </div>
                            <span class="text-[10px] text-gray-400 ml-1">${formatTime()}</span>
                        </div>
                    </div>
                `;
                $messages.append(html);
                lucide.createIcons(); // Re-init icons for new message
                scrollToBottom();
            }

            // Markdown rendering helper using Laravel's Str::markdown via AJAX
            window.renderMarkdown = function(text) {
                let html = text;
                $.ajax({
                    url: '/api/markdown',
                    method: 'POST',
                    data: { text: text, _token: '{{ csrf_token() }}' },
                    async: false,
                    success: function(response) {
                        html = response.html;
                    }
                });
                return html;
            };


            // --- Logic & Data Flow ---

            // Generate or get session ID
            function getSessionId() {
                let sessionId = localStorage.getItem('chatbot-session-id');
                if (!sessionId) {
                    sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    localStorage.setItem('chatbot-session-id', sessionId);
                }
                return sessionId;
            }

            function loadPreviousMessages() {
                const sessionId = getSessionId();

                $.ajax({
                    url: '/api/chat/messages',
                    method: 'GET',
                    data: { session_id: sessionId },
                    success: function(response) {
                        if (response.messages && response.messages.length > 0) {
                            response.messages.forEach(function(msg) {
                                if (msg.role === 'user') {
                                    appendUserMessage(msg.message);
                                } else if (msg.role === 'bot') {
                                    appendBotMessage(msg.message);
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('No previous messages or error loading:', error);
                    }
                });
            }

            // Send message to backend API
            window.sendMessageToBackend = function(text) {
                isThinking = true;
                updateInputState();
                $typingIndicator.removeClass('hidden');
                scrollToBottom();

                const sessionId = getSessionId();
                const interest = localStorage.getItem('chatbot-interest') || '';
                const mode = chatMode || 'default';

                $.ajax({
                    url: '/api/chat/send',
                    method: 'POST',
                    data: {
                        session_id: sessionId,
                        message: text,
                        interest: interest,
                        mode: mode,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $typingIndicator.addClass('hidden');
                        isThinking = false;
                        updateInputState();
                        if (response.message) {
                            appendBotMessage(escapeHtml(response.message));
                        } else {
                            appendBotMessage('Sorry, I did not get a response.');
                        }
                    },
                    error: function(xhr, status, error) {
                        $typingIndicator.addClass('hidden');
                        isThinking = false;
                        updateInputState();
                        appendBotMessage('Sorry, there was an error sending your message.');
                    }
                });
            }

            function updateInputState() {
                if (isThinking) {
                    $input.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
                    $sendBtn.prop('disabled', true);
                } else {
                    $input.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    // Send button enablement is handled by input listener
                    checkInputEmpty(); 
                    $input.focus();
                }
            }

            function checkInputEmpty() {
                if ($input.val().trim() === "" || isThinking) {
                    $sendBtn.prop('disabled', true);
                } else {
                    $sendBtn.prop('disabled', false);
                }
            }

            function escapeHtml(text) {
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // --- Event Listeners ---

            $input.on('input', function() {
                checkInputEmpty();
            });

            $form.on('submit', function(e) {
                e.preventDefault();
                const text = $input.val().trim();
                
                if (text && !isThinking) {
                    appendUserMessage(text);
                    $input.val(''); // Clear input
                    checkInputEmpty(); // Reset button state
                    sendMessageToBackend(text);
                }
            });

        // Interest selector functionality
        let selectedInterest = localStorage.getItem('chatbot-interest') || 'general';
        
        // Set default interest if not already set
        if (!localStorage.getItem('chatbot-interest')) {
            localStorage.setItem('chatbot-interest', 'general');
        }
        
        window.selectInterest = function(interest) {
            selectedInterest = interest;
            localStorage.setItem('chatbot-interest', interest);
            
            // Update checkmarks
            document.querySelectorAll('[id^="check-"]').forEach(el => {
                el.style.display = 'none';
            });
            
            if (interest) {
                const checkEl = document.getElementById('check-' + interest);
                if (checkEl) checkEl.style.display = 'block';
            } else {
                document.getElementById('check-all').style.display = 'block';
            }
            
            // Close dropdown
            document.getElementById('interest-dropdown').classList.add('hidden');
            
            // Show feedback
            showInterestToast(interest);
        }
        
        function showInterestToast(interest) {
            const interestText = interest ? interest.charAt(0).toUpperCase() + interest.slice(1) : 'All Interests';
            // You can implement a toast notification here if desired
            console.log('Interest set to:', interestText);
        }
        
        // Initialize selected interest on load
        document.addEventListener('DOMContentLoaded', function() {
            if (selectedInterest && selectedInterest !== '') {
                const checkEl = document.getElementById('check-' + selectedInterest);
                if (checkEl) checkEl.style.display = 'block';
            } else {
                // Default to general if nothing set
                selectedInterest = 'general';
                localStorage.setItem('chatbot-interest', 'general');
                document.getElementById('check-general').style.display = 'block';
            }
        });
            
            // Toggle dropdown
            $('#interest-toggle').on('click', function(e) {
                e.stopPropagation();
                $('#interest-dropdown').toggleClass('hidden');
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#interest-toggle, #interest-dropdown').length) {
                    $('#interest-dropdown').addClass('hidden');
                }
            });
             // Mode selection logic
        let chatMode = localStorage.getItem('chatMode') || null;
        function setMode(mode) {
            chatMode = mode;
            localStorage.setItem('chatMode', mode);
            $('#mode-selector').hide();
        }

            // Mode selector logic
            if (!chatMode) {
                $('#mode-selector').show();
            } else {
                $('#mode-selector').hide();
            }
            $('.mode-btn').on('click', function() {
                setMode($(this).data('mode'));
            });
            // Initial load
            setTimeout(() => {
                loadPreviousMessages();
                scrollToBottom();
                $input.focus();
            }, 500);
        });
    </script>
</body>
</html>