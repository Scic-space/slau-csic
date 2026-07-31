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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['general', 'urgent', 'event', 'meeting', 'achievement']);
            $table->enum('audience', ['all', 'active_members', 'board', 'specific_roles']); // Who should see it
            $table->json('target_roles')->nullable(); // If audience = specific_roles
            $table->boolean('is_published')->default(false);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_push')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
