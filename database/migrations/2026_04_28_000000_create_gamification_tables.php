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
        // 1. Create point_transactions table
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('reason');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });

        // 2. Create badges table
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon');
            $table->enum('criteria_type', [
                'events_attended',
                'ctf_completed',
                'total_points',
                'teaching_sessions',
                'streak_days',
                'ctf_score',
                'custom',
            ]);
            $table->integer('criteria_value')->default(0);
            $table->integer('points_bonus')->default(0);
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->timestamps();
        });

        // 3. Create user_badges pivot table
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });

        // 4. Add rank columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rank', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze')->after('score');
            $table->timestamp('rank_changed_at')->nullable()->after('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rank', 'rank_changed_at']);
        });

        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('point_transactions');
    }
};
