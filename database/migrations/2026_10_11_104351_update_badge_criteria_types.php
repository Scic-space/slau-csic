<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->enum('criteria_type', [
                'events_attended',
                'ctf_completed',
                'total_points',
                'teaching_sessions',
                'streak_days',
                'ctf_score',
                'club_tracks',
                'exam_passed',
                'competition',
                'custom',
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->enum('criteria_type', [
                'events_attended',
                'ctf_completed',
                'total_points',
                'teaching_sessions',
                'streak_days',
                'ctf_score',
                'custom',
            ])->change();
        });
    }
};
