<?php

/**
 * System Settings Module - Usage Examples
 * 
 * This file demonstrates various ways to use the System Settings module
 * in your Laravel application.
 */

namespace App\Examples;

use App\Services\SystemSettingService;
use Illuminate\Support\Facades\View;

class SystemSettingsUsageExamples
{
    /**
     * Example 1: Using Helper Function in Controllers
     */
    public function example1_HelperInController()
    {
        // Get simple text setting
        $appName = system_setting('app_name', 'My App');
        
        // Get numeric setting
        $maxTokens = system_setting('ai_max_tokens', 2048);
        
        // Get boolean setting
        $enableLogging = system_setting('enable_chat_logging', '1');
        
        // Use in logic
        if ($enableLogging === '1') {
            // Log chat messages
        }
        
        return view('welcome', compact('appName'));
    }

    /**
     * Example 2: Using Service Injection
     */
    public function example2_ServiceInjection(SystemSettingService $settingService)
    {
        // Get setting via service
        $temperature = $settingService->get('ai_temperature', 0.7);
        
        // Set setting via service
        $settingService->set('ai_temperature', 0.8, 'number');
        
        // Get provider settings
        $defaultProvider = $settingService->get('default_ai_provider', 'openai');
        $fallbackProvider = $settingService->get('fallback_ai_provider', 'gemini');
        
        return response()->json([
            'temperature' => $temperature,
            'provider' => $defaultProvider,
            'fallback' => $fallbackProvider,
        ]);
    }

    /**
     * Example 3: Using in Blade Views
     */
    public function example3_BladeViews()
    {
        /**
         * In your blade file (e.g., welcome.blade.php):
         * 
         * <h1>{{ system_setting('app_name', 'Chatbot') }}</h1>
         * <p>{{ system_setting('app_tagline', 'Your AI Assistant') }}</p>
         * 
         * @if(system_setting('show_typing_indicator') == '1')
         *     <div class="typing-indicator">...</div>
         * @endif
         * 
         * <img src="{{ asset('storage/' . system_setting('app_logo')) }}" alt="Logo">
         */
    }

    /**
     * Example 4: Using in API Configuration
     */
    public function example4_ApiConfiguration()
    {
        $apiConfig = [
            'provider' => system_setting('default_ai_provider', 'openai'),
            'temperature' => (float) system_setting('ai_temperature', 0.7),
            'max_tokens' => (int) system_setting('ai_max_tokens', 2048),
            'system_prompt' => system_setting('system_persona', 'You are a helpful assistant.'),
        ];
        
        return $apiConfig;
    }

    /**
     * Example 5: Using in Middleware/Rate Limiting
     */
    public function example5_RateLimiting()
    {
        $rateLimit = (int) system_setting('rate_limit_per_minute', 60);
        
        // Use in rate limiter configuration
        // \Illuminate\Support\Facades\RateLimiter::for('api', function ($request) use ($rateLimit) {
        //     return Limit::perMinute($rateLimit);
        // });
        
        return $rateLimit;
    }

    /**
     * Example 6: Using in Document Processing
     */
    public function example6_DocumentProcessing()
    {
        $maxSize = (int) system_setting('max_document_size_mb', 10);
        $allowedTypes = explode(',', system_setting('allowed_document_types', 'pdf,doc,docx'));
        $chunkSize = (int) system_setting('default_chunk_size', 1000);
        $overlap = (int) system_setting('chunk_overlap', 200);
        
        // Validate file upload
        $validationRules = [
            'document' => 'required|file|max:' . ($maxSize * 1024),
            'document' => 'mimes:' . implode(',', $allowedTypes),
        ];
        
        return [
            'max_size' => $maxSize,
            'allowed_types' => $allowedTypes,
            'chunk_size' => $chunkSize,
            'overlap' => $overlap,
        ];
    }

    /**
     * Example 7: Using in Chat Interface
     */
    public function example7_ChatInterface()
    {
        return [
            'welcome_message' => system_setting('chat_welcome_message', 'Hello!'),
            'placeholder' => system_setting('chat_placeholder_text', 'Type your message...'),
            'theme' => system_setting('chat_theme', 'light'),
            'show_typing' => system_setting('show_typing_indicator', '1') === '1',
            'history_limit' => (int) system_setting('chat_history_limit', 50),
        ];
    }

    /**
     * Example 8: Using in Email Configuration
     */
    public function example8_EmailConfiguration()
    {
        $appName = system_setting('app_name', 'Chatbot');
        $primaryColor = system_setting('primary_color', '#3B82F6');
        
        // Use in email templates
        // config(['mail.from.name' => $appName]);
        
        return view('emails.welcome', [
            'app_name' => $appName,
            'primary_color' => $primaryColor,
        ]);
    }

    /**
     * Example 9: Using in Background Jobs
     */
    public function example9_BackgroundJobs()
    {
        $processingMode = system_setting('document_processing_mode', 'full');
        $enableLogging = system_setting('enable_chat_logging', '1') === '1';
        
        if ($processingMode === 'full') {
            // Process entire document
        } else {
            // Process keywords only
        }
        
        if ($enableLogging) {
            // Log job execution
        }
    }

    /**
     * Example 10: Using in Service Providers
     */
    public function example10_ServiceProvider()
    {
        /**
         * In AppServiceProvider.php boot() method:
         * 
         * View::composer('*', function ($view) {
         *     $view->with('appName', system_setting('app_name', 'Chatbot'));
         *     $view->with('appLogo', system_setting('app_logo'));
         *     $view->with('primaryColor', system_setting('primary_color', '#3B82F6'));
         * });
         * 
         * Now these variables are available in all views
         */
    }

    /**
     * Example 11: Updating Settings Programmatically
     */
    public function example11_UpdateSettings()
    {
        // Update a single setting
        set_system_setting('app_name', 'New App Name', 'text');
        
        // Update multiple settings
        $settings = [
            ['key' => 'ai_temperature', 'value' => 0.9, 'type' => 'number'],
            ['key' => 'chat_history_limit', 'value' => 100, 'type' => 'number'],
            ['key' => 'enable_thinking_mode', 'value' => '1', 'type' => 'boolean'],
        ];
        
        foreach ($settings as $setting) {
            set_system_setting($setting['key'], $setting['value'], $setting['type']);
        }
    }

    /**
     * Example 12: Using in Config Files
     */
    public function example12_ConfigFiles()
    {
        /**
         * In config/app.php:
         * 
         * 'name' => env('APP_NAME', system_setting('app_name', 'Laravel')),
         * 'timezone' => env('APP_TIMEZONE', system_setting('timezone', 'UTC')),
         * 
         * Note: Be careful with config caching when using settings in config files
         */
    }

    /**
     * Example 13: Using in API Responses
     */
    public function example13_ApiResponses()
    {
        return response()->json([
            'app_info' => [
                'name' => system_setting('app_name'),
                'tagline' => system_setting('app_tagline'),
                'description' => system_setting('app_description'),
            ],
            'chat_config' => [
                'welcome_message' => system_setting('chat_welcome_message'),
                'placeholder' => system_setting('chat_placeholder_text'),
                'theme' => system_setting('chat_theme'),
            ],
            'ai_config' => [
                'provider' => system_setting('default_ai_provider'),
                'temperature' => (float) system_setting('ai_temperature'),
                'max_tokens' => (int) system_setting('ai_max_tokens'),
            ],
        ]);
    }

    /**
     * Example 14: Using with Validation
     */
    public function example14_Validation()
    {
        $maxSize = (int) system_setting('max_document_size_mb', 10);
        $allowedTypes = explode(',', system_setting('allowed_document_types', 'pdf,doc'));
        
        $rules = [
            'document' => [
                'required',
                'file',
                'max:' . ($maxSize * 1024),
                'mimes:' . implode(',', array_map('trim', $allowedTypes)),
            ],
        ];
        
        return $rules;
    }

    /**
     * Example 15: Using in Scheduled Tasks
     */
    public function example15_ScheduledTasks()
    {
        $retentionDays = (int) system_setting('log_retention_days', 30);
        
        // In app/Console/Kernel.php:
        // $schedule->call(function () use ($retentionDays) {
        //     DB::table('chat_logs')
        //         ->where('created_at', '<', now()->subDays($retentionDays))
        //         ->delete();
        // })->daily();
    }

    /**
     * Example 16: Caching Considerations
     */
    public function example16_Caching()
    {
        // Settings are cached for 1 hour automatically
        $value1 = system_setting('app_name'); // Hits database, then cached
        $value2 = system_setting('app_name'); // Served from cache
        
        // After updating a setting:
        set_system_setting('app_name', 'New Name', 'text'); // Cache is cleared
        
        // Next call hits database again:
        $value3 = system_setting('app_name'); // Fresh from database
    }

    /**
     * Example 17: Type Casting
     */
    public function example17_TypeCasting()
    {
        // Always cast to appropriate type
        $temperature = (float) system_setting('ai_temperature', 0.7);
        $maxTokens = (int) system_setting('ai_max_tokens', 2048);
        $isEnabled = system_setting('enable_chat_logging', '1') === '1';
        
        // For arrays/JSON (future feature)
        $allowedTypes = explode(',', system_setting('allowed_document_types', 'pdf,doc'));
    }

    /**
     * Example 18: Using in Frontend (via API)
     */
    public function example18_FrontendApi()
    {
        // Create an API endpoint for frontend
        return response()->json([
            'settings' => [
                'app_name' => system_setting('app_name'),
                'app_logo' => system_setting('app_logo') 
                    ? asset('storage/' . system_setting('app_logo')) 
                    : null,
                'primary_color' => system_setting('primary_color'),
                'chat_welcome' => system_setting('chat_welcome_message'),
                'chat_placeholder' => system_setting('chat_placeholder_text'),
                'chat_theme' => system_setting('chat_theme'),
            ],
        ]);
    }
}
