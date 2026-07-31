<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(['start_date', 'status']);
            $table->index(['type', 'status']);
            $table->index('organizer_id');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'status']);
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['organizer_id']);
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
        });
    }
};
