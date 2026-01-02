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
       Schema::create('user_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('category_id')->nullable()->constrained('expert_categories');
    $table->decimal('monthly_fee', 10, 2);
    $table->string('stripe_subscription_id')->unique();
    $table->string('stripe_customer_id');
    $table->timestamp('current_period_start');
    $table->timestamp('current_period_end');
    $table->boolean('active')->default(true);
    $table->boolean('auto_renew')->default(true);
    $table->timestamp('canceled_at')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'category_id']); // one active sub per category if needed
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
