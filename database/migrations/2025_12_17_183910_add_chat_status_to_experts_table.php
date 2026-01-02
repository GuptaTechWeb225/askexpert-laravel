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
       Schema::table('experts', function (Blueprint $table) {
    $table->boolean('is_online')->default(false);
    $table->boolean('is_busy')->default(false);
    $table->timestamp('last_active_at')->nullable();
    $table->foreignId('current_chat_id')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            //
        });
    }
};
