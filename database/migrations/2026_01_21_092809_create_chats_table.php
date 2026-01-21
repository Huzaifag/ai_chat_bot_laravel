<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('session_id'); // Unique chat session identifier (UUID or random string)
            $table->unsignedBigInteger('document_id')->nullable(); // Optional: related document
            $table->enum('role', ['user', 'bot']); // Who sent the message
            $table->text('message'); // Message content
            $table->timestamps();

            // Indexes
            $table->index('session_id');
            $table->foreign('document_id')
                  ->references('id')->on('documents')
                  ->onDelete('cascade'); // optional: delete chat if document deleted
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
