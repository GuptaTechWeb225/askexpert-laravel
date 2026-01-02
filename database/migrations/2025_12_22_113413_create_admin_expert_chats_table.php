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
        Schema::create('admin_expert_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins'); // ya users table mein admin role
            $table->foreignId('expert_id')->constrained('experts');
            $table->unique(['admin_id', 'expert_id']); // ek hi chat session
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_expert_chats');
    }
};
