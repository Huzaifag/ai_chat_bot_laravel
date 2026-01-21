<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // General Settings
            ['key' => 'app_name', 'value' => 'Chatbot', 'type' => 'text'],
            ['key' => 'app_tagline', 'value' => 'Your AI Assistant', 'type' => 'text'],
            ['key' => 'app_description', 'value' => 'An intelligent chatbot powered by AI to assist you with your queries.', 'type' => 'textarea'],
            ['key' => 'app_logo', 'value' => '', 'type' => 'image'],
            ['key' => 'app_favicon', 'value' => '', 'type' => 'image'],
            ['key' => 'primary_color', 'value' => '#3B82F6', 'type' => 'text'],
            ['key' => 'secondary_color', 'value' => '#10B981', 'type' => 'text'],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'select'],

            // AI & Chat Settings
            ['key' => 'default_ai_provider', 'value' => 'openai', 'type' => 'select'],
            ['key' => 'fallback_ai_provider', 'value' => 'gemini', 'type' => 'select'],
            ['key' => 'ai_temperature', 'value' => '0.7', 'type' => 'number'],
            ['key' => 'ai_max_tokens', 'value' => '2048', 'type' => 'number'],
            ['key' => 'chat_history_limit', 'value' => '50', 'type' => 'number'],
            ['key' => 'system_persona', 'value' => 'You are a helpful AI assistant. You provide accurate, helpful, and friendly responses to user queries.', 'type' => 'textarea'],
            ['key' => 'enable_thinking_mode', 'value' => '0', 'type' => 'boolean'],

            // Document Settings
            ['key' => 'max_document_size_mb', 'value' => '10', 'type' => 'number'],
            ['key' => 'allowed_document_types', 'value' => 'pdf,doc,docx,txt', 'type' => 'text'],
            ['key' => 'default_chunk_size', 'value' => '1000', 'type' => 'number'],
            ['key' => 'chunk_overlap', 'value' => '200', 'type' => 'number'],
            ['key' => 'document_processing_mode', 'value' => 'full', 'type' => 'select'],

            // Appearance Settings
            ['key' => 'chat_welcome_message', 'value' => 'Hello! How can I help you today?', 'type' => 'textarea'],
            ['key' => 'chat_placeholder_text', 'value' => 'Type your message...', 'type' => 'text'],
            ['key' => 'chat_theme', 'value' => 'light', 'type' => 'select'],
            ['key' => 'show_typing_indicator', 'value' => '1', 'type' => 'boolean'],

            // Security Settings
            ['key' => 'enable_chat_logging', 'value' => '1', 'type' => 'boolean'],
            ['key' => 'log_retention_days', 'value' => '30', 'type' => 'number'],
            ['key' => 'rate_limit_per_minute', 'value' => '60', 'type' => 'number'],
        ];

        foreach ($defaultSettings as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type']
                ]
            );
        }

        $this->command->info('System settings seeded successfully!');
    }
}
