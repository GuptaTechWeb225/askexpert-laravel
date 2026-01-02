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
        Schema::create('admin_expert_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('admin_expert_chat_id')->constrained();
    $table->unsignedBigInteger('sender_id');
    $table->string('sender_type'); // 'admin' or 'expert'
    $table->text('message')->nullable();
    $table->string('image_path')->nullable(); // image ke liye alag column
    $table->boolean('is_read')->default(false);
    $table->timestamp('sent_at');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_expert_messages');
    }
};
