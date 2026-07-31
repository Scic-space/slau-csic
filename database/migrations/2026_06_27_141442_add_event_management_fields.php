<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('virtual_link')->nullable()->after('external_link');
            $table->string('visibility', 20)->default('members_only')->after('is_public');
            $table->string('registration_type', 20)->default('first_come')->after('registration_deadline');
            $table->text('learning_objectives')->nullable()->after('requirements');
            $table->string('skill_level', 20)->nullable()->after('learning_objectives');
            $table->foreignId('instructor_id')->nullable()->constrained('users')->after('organizer_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instructor_id');
            $table->dropColumn(['virtual_link', 'visibility', 'registration_type', 'learning_objectives', 'skill_level']);
        });
    }
};
