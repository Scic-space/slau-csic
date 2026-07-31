<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_id')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('program')->nullable();
            $table->string('faculty')->nullable();
            $table->unsignedTinyInteger('year_of_study')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('residence')->nullable();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('active');
            $table->string('status')->default('pending');
            $table->text('status_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->timestamp('suspended_until')->nullable();
            $table->foreignId('suspended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('github_username')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('discord_username')->nullable();
            $table->boolean('is_discord_member')->default(false);
            $table->timestamps();
        });

        Schema::create('user_privacies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_discord')->default(false);
            $table->boolean('show_attendance')->default(true);
            $table->boolean('show_program')->default(true);
            $table->boolean('show_year')->default(true);
            $table->boolean('show_profile')->default(true);
            $table->boolean('allow_contact')->default(false);
            $table->timestamps();
        });

        Schema::create('gamification_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('attendance_count')->default(0);
            $table->integer('total_sessions_attended')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->integer('bonus_points')->default(0);
            $table->decimal('score', 8, 2)->default(0);
            $table->string('rank')->default('bronze');
            $table->timestamp('rank_changed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamification_stats');
        Schema::dropIfExists('user_privacies');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('member_profiles');
    }
};
