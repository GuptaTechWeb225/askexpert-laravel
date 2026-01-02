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
        Schema::create('expert_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('expert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('expert_categories');
            $table->decimal('basic_amount', 10, 2)->default(0);
            $table->decimal('premium_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending'); // for payout status
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable(); // optional, like "low review" etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_earnings');
    }
};
