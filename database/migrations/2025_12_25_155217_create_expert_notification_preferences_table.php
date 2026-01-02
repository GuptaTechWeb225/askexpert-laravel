<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->enum('type', [
                'new_question_assigned',
                'payout_processed',
                'admin_message',
                'system_updates'
            ]);
            $table->boolean('email')->default(true);
            $table->boolean('sms')->default(false);
            $table->boolean('dashboard')->default(true);
            $table->timestamps();

            $table->unique(['expert_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_notification_preferences');
    }
};