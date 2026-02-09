<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatSession;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ChatAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates sample chat data with API usage metrics for testing analytics
     */
    public function run(): void
    {
        // Create sample sessions
        $sessions = [
            'session-' . uniqid(),
            'session-' . uniqid(),
            'session-' . uniqid(),
        ];

        foreach ($sessions as $sessionId) {
            ChatSession::create([
                'session_id' => $sessionId,
                'user_id' => null,
            ]);
        }

        // Generate chat data for the last 30 days
        $providers = ['gemini', 'openai'];
        
        for ($day = 30; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            $chatsPerDay = rand(2, 8);
            
            for ($i = 0; $i < $chatsPerDay; $i++) {
                $sessionId = $sessions[array_rand($sessions)];
                $provider = $providers[array_rand($providers)];
                
                // Create user message
                Chat::create([
                    'session_id' => $sessionId,
                    'role' => 'user',
                    'message' => 'Sample user question ' . $i,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
                
                // Create bot response with API metrics
                $tokens = rand(100, 1500);
                $cost = $provider === 'gemini' 
                    ? ($tokens / 1000) * 0.00025 
                    : ($tokens / 1000) * 0.0015;
                
                Chat::create([
                    'session_id' => $sessionId,
                    'role' => 'bot',
                    'message' => 'Sample bot response with AI generated content',
                    'api_provider' => $provider,
                    'api_tokens_used' => $tokens,
                    'api_cost' => $cost,
                    'response_time_ms' => rand(500, 3000),
                    'created_at' => $date->copy()->addSeconds(2),
                    'updated_at' => $date->copy()->addSeconds(2),
                ]);
            }
        }

        $this->command->info('Chat analytics sample data created successfully!');
    }
}
