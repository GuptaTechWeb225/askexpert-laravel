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
        Schema::create('expert_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('expert_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating')->comment('1 to 5 stars');
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['chat_session_id']); // Ek chat ke liye ek review
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_reviews');
    }
};
