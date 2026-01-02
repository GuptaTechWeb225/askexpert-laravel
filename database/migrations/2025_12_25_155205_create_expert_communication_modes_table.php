<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_communication_modes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->enum('mode', ['text_chat', 'voice_call', 'video_call']);
            $table->boolean('available')->default(true);
            $table->boolean('on_break')->default(false);
            $table->boolean('vacation_mode')->default(false);
            $table->timestamps();

            $table->unique(['expert_id', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_communication_modes');
    }
};