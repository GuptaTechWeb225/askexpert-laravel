<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('chat_session_id');
    $table->enum('sender_type', ['user', 'expert', 'system']);
    $table->unsignedBigInteger('sender_id')->nullable();
    $table->text('message');
    $table->timestamp('sent_at');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
