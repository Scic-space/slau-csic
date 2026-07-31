<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('waitlist_enabled')->default(false)->after('registration_required');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->timestamp('waitlisted_at')->nullable()->after('registered_at');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('waitlist_enabled');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn('waitlisted_at');
        });
    }
};
