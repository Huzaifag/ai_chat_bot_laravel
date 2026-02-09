<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->string('api_provider')->nullable()->after('message'); // e.g., 'gemini', 'openai'
            $table->integer('api_tokens_used')->default(0)->after('api_provider'); // Tokens consumed
            $table->decimal('api_cost', 10, 6)->default(0)->after('api_tokens_used'); // Estimated cost
            $table->integer('response_time_ms')->nullable()->after('api_cost'); // Response time in milliseconds
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['api_provider', 'api_tokens_used', 'api_cost', 'response_time_ms']);
        });
    }
};
