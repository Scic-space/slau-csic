<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('event_reminders')->default(true);
            $table->boolean('event_cancellations')->default(true);
            $table->boolean('challenge_solved')->default(true);
            $table->boolean('membership_updates')->default(true);
            $table->boolean('broadcast_messages')->default(true);
            $table->boolean('fine_notifications')->default(true);
            $table->boolean('weekly_digest')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
