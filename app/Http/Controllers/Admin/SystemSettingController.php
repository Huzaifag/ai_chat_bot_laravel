<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SystemSettingController extends Controller
{
    protected $settingService;

    public function __construct(SystemSettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index()
    {
        $settings = $this->getGroupedSettings();
        
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validationRules = $this->getValidationRules($request);
        
        $validator = Validator::make($request->all(), $validationRules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            $type = $this->getSettingType($key);
            
            // Handle file uploads
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                
                // Delete old file if exists
                $oldValue = $this->settingService->get($key);
                if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                    Storage::disk('public')->delete($oldValue);
                }
                
                // Store new file
                $path = $file->store('settings', 'public');
                $value = $path;
            }
            
            // Handle boolean values
            if ($type === 'boolean') {
                $value = $request->has($key) ? '1' : '0';
            }
            
            // Handle JSON values
            if ($type === 'json' && is_array($value)) {
                $value = json_encode($value);
            }
            
            $this->settingService->set($key, $value, $type);
        }
        
        // Handle unchecked boolean fields
        $booleanFields = $this->getBooleanFields();
        foreach ($booleanFields as $field) {
            if (!$request->has($field)) {
                $this->settingService->set($field, '0', 'boolean');
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    protected function getGroupedSettings(): array
    {
        return [
            'general' => [
                [
                    'key' => 'app_name',
                    'label' => 'Application Name',
                    'type' => 'text',
                    'value' => $this->settingService->get('app_name', 'Chatbot'),
                    'placeholder' => 'Enter application name',
                ],
                [
                    'key' => 'app_tagline',
                    'label' => 'Application Tagline',
                    'type' => 'text',
                    'value' => $this->settingService->get('app_tagline', 'Your AI Assistant'),
                    'placeholder' => 'Enter tagline',
                ],
                [
                    'key' => 'app_description',
                    'label' => 'Application Description',
                    'type' => 'textarea',
                    'value' => $this->settingService->get('app_description', ''),
                    'placeholder' => 'Enter application description',
                ],
                [
                    'key' => 'app_logo',
                    'label' => 'Application Logo',
                    'type' => 'image',
                    'value' => $this->settingService->get('app_logo', ''),
                    'placeholder' => '',
                ],
                [
                    'key' => 'app_favicon',
                    'label' => 'Application Favicon',
                    'type' => 'image',
                    'value' => $this->settingService->get('app_favicon', ''),
                    'placeholder' => '',
                ],
                [
                    'key' => 'primary_color',
                    'label' => 'Primary Color',
                    'type' => 'text',
                    'value' => $this->settingService->get('primary_color', '#3B82F6'),
                    'placeholder' => '#3B82F6',
                ],
                [
                    'key' => 'secondary_color',
                    'label' => 'Secondary Color',
                    'type' => 'text',
                    'value' => $this->settingService->get('secondary_color', '#10B981'),
                    'placeholder' => '#10B981',
                ],
                [
                    'key' => 'timezone',
                    'label' => 'Timezone',
                    'type' => 'select',
                    'value' => $this->settingService->get('timezone', 'UTC'),
                    'options' => [
                        'UTC' => 'UTC',
                        'America/New_York' => 'America/New York',
                        'America/Chicago' => 'America/Chicago',
                        'America/Los_Angeles' => 'America/Los Angeles',
                        'Europe/London' => 'Europe/London',
                        'Europe/Paris' => 'Europe/Paris',
                        'Asia/Tokyo' => 'Asia/Tokyo',
                        'Asia/Shanghai' => 'Asia/Shanghai',
                        'Australia/Sydney' => 'Australia/Sydney',
                    ],
                ],
            ],
            'ai_chat' => [
                [
                    'key' => 'default_ai_provider',
                    'label' => 'Default AI Provider',
                    'type' => 'select',
                    'value' => $this->settingService->get('default_ai_provider', 'openai'),
                    'options' => [
                        'openai' => 'OpenAI',
                        'gemini' => 'Google Gemini',
                        'anthropic' => 'Anthropic',
                    ],
                ],
                [
                    'key' => 'fallback_ai_provider',
                    'label' => 'Fallback AI Provider',
                    'type' => 'select',
                    'value' => $this->settingService->get('fallback_ai_provider', 'gemini'),
                    'options' => [
                        'openai' => 'OpenAI',
                        'gemini' => 'Google Gemini',
                        'anthropic' => 'Anthropic',
                    ],
                ],
                [
                    'key' => 'ai_temperature',
                    'label' => 'AI Temperature',
                    'type' => 'number',
                    'value' => $this->settingService->get('ai_temperature', '0.7'),
                    'placeholder' => '0.7',
                    'step' => '0.1',
                    'min' => '0',
                    'max' => '2',
                ],
                [
                    'key' => 'ai_max_tokens',
                    'label' => 'Max Tokens',
                    'type' => 'number',
                    'value' => $this->settingService->get('ai_max_tokens', '2048'),
                    'placeholder' => '2048',
                ],
                [
                    'key' => 'chat_history_limit',
                    'label' => 'Chat History Limit',
                    'type' => 'number',
                    'value' => $this->settingService->get('chat_history_limit', '50'),
                    'placeholder' => '50',
                ],
                [
                    'key' => 'system_persona',
                    'label' => 'System Persona',
                    'type' => 'textarea',
                    'value' => $this->settingService->get('system_persona', 'You are a helpful AI assistant.'),
                    'placeholder' => 'Describe the AI personality and behavior',
                ],
                [
                    'key' => 'enable_thinking_mode',
                    'label' => 'Enable Thinking Mode',
                    'type' => 'boolean',
                    'value' => $this->settingService->get('enable_thinking_mode', '0'),
                ],
            ],
            'documents' => [
                [
                    'key' => 'max_document_size_mb',
                    'label' => 'Max Document Size (MB)',
                    'type' => 'number',
                    'value' => $this->settingService->get('max_document_size_mb', '10'),
                    'placeholder' => '10',
                ],
                [
                    'key' => 'allowed_document_types',
                    'label' => 'Allowed Document Types',
                    'type' => 'text',
                    'value' => $this->settingService->get('allowed_document_types', 'pdf,doc,docx,txt'),
                    'placeholder' => 'pdf,doc,docx,txt',
                    'help' => 'Comma-separated list of file extensions',
                ],
                [
                    'key' => 'default_chunk_size',
                    'label' => 'Default Chunk Size',
                    'type' => 'number',
                    'value' => $this->settingService->get('default_chunk_size', '1000'),
                    'placeholder' => '1000',
                ],
                [
                    'key' => 'chunk_overlap',
                    'label' => 'Chunk Overlap',
                    'type' => 'number',
                    'value' => $this->settingService->get('chunk_overlap', '200'),
                    'placeholder' => '200',
                ],
                [
                    'key' => 'document_processing_mode',
                    'label' => 'Document Processing Mode',
                    'type' => 'select',
                    'value' => $this->settingService->get('document_processing_mode', 'full'),
                    'options' => [
                        'full' => 'Full Processing',
                        'keyword' => 'Keyword Only',
                    ],
                ],
            ],
            'appearance' => [
                [
                    'key' => 'chat_welcome_message',
                    'label' => 'Chat Welcome Message',
                    'type' => 'textarea',
                    'value' => $this->settingService->get('chat_welcome_message', 'Hello! How can I help you today?'),
                    'placeholder' => 'Enter welcome message',
                ],
                [
                    'key' => 'chat_placeholder_text',
                    'label' => 'Chat Placeholder Text',
                    'type' => 'text',
                    'value' => $this->settingService->get('chat_placeholder_text', 'Type your message...'),
                    'placeholder' => 'Enter placeholder text',
                ],
                [
                    'key' => 'chat_theme',
                    'label' => 'Chat Theme',
                    'type' => 'select',
                    'value' => $this->settingService->get('chat_theme', 'light'),
                    'options' => [
                        'light' => 'Light',
                        'dark' => 'Dark',
                        'auto' => 'Auto',
                    ],
                ],
                [
                    'key' => 'show_typing_indicator',
                    'label' => 'Show Typing Indicator',
                    'type' => 'boolean',
                    'value' => $this->settingService->get('show_typing_indicator', '1'),
                ],
            ],
            'security' => [
                [
                    'key' => 'enable_chat_logging',
                    'label' => 'Enable Chat Logging',
                    'type' => 'boolean',
                    'value' => $this->settingService->get('enable_chat_logging', '1'),
                ],
                [
                    'key' => 'log_retention_days',
                    'label' => 'Log Retention Days',
                    'type' => 'number',
                    'value' => $this->settingService->get('log_retention_days', '30'),
                    'placeholder' => '30',
                ],
                [
                    'key' => 'rate_limit_per_minute',
                    'label' => 'Rate Limit Per Minute',
                    'type' => 'number',
                    'value' => $this->settingService->get('rate_limit_per_minute', '60'),
                    'placeholder' => '60',
                ],
            ],
        ];
    }

    protected function getValidationRules(Request $request): array
    {
        $rules = [];

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            $type = $this->getSettingType($key);
            
            switch ($type) {
                case 'text':
                    $rules[$key] = 'nullable|string|max:255';
                    break;
                case 'textarea':
                    $rules[$key] = 'nullable|string';
                    break;
                case 'number':
                    $rules[$key] = 'nullable|numeric';
                    break;
                case 'boolean':
                    $rules[$key] = 'nullable|boolean';
                    break;
                case 'image':
                    $rules[$key] = 'nullable|image|max:2048';
                    break;
                case 'select':
                    $rules[$key] = 'nullable|string';
                    break;
                case 'json':
                    $rules[$key] = 'nullable';
                    break;
            }
        }

        return $rules;
    }

    protected function getSettingType(string $key): string
    {
        $typeMap = [
            'app_name' => 'text',
            'app_tagline' => 'text',
            'app_description' => 'textarea',
            'app_logo' => 'image',
            'app_favicon' => 'image',
            'primary_color' => 'text',
            'secondary_color' => 'text',
            'timezone' => 'select',
            'default_ai_provider' => 'select',
            'fallback_ai_provider' => 'select',
            'ai_temperature' => 'number',
            'ai_max_tokens' => 'number',
            'chat_history_limit' => 'number',
            'system_persona' => 'textarea',
            'enable_thinking_mode' => 'boolean',
            'max_document_size_mb' => 'number',
            'allowed_document_types' => 'text',
            'default_chunk_size' => 'number',
            'chunk_overlap' => 'number',
            'document_processing_mode' => 'select',
            'chat_welcome_message' => 'textarea',
            'chat_placeholder_text' => 'text',
            'chat_theme' => 'select',
            'show_typing_indicator' => 'boolean',
            'enable_chat_logging' => 'boolean',
            'log_retention_days' => 'number',
            'rate_limit_per_minute' => 'number',
        ];

        return $typeMap[$key] ?? 'text';
    }

    protected function getBooleanFields(): array
    {
        return [
            'enable_thinking_mode',
            'show_typing_indicator',
            'enable_chat_logging',
        ];
    }
}
