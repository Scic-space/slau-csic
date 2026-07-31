<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Event-to-CTF linkage
        Schema::table('ctf_competitions', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete()->after('id');
            $table->boolean('allow_teams')->default(false)->after('max_score');
            $table->integer('max_team_size')->default(5)->after('allow_teams');
        });

        // 2. CTF Teams
        Schema::create('ctf_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_competition_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('invite_code', 32)->unique()->nullable();
            $table->foreignId('captain_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_open')->default(true);
            $table->timestamps();
            $table->unique(['ctf_competition_id', 'name']);
        });

        Schema::create('ctf_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['captain', 'member'])->default('member');
            $table->timestamps();
            $table->unique(['ctf_team_id', 'user_id']);
        });

        // 3. Challenge file attachments
        Schema::create('ctf_challenge_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_challenge_id')->constrained()->onDelete('cascade');
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type', 127)->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });

        // 4. Hint purchases
        Schema::create('ctf_hint_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_challenge_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points_spent');
            $table->timestamp('purchased_at');
            $table->timestamps();
            $table->unique(['ctf_challenge_id', 'user_id']);
        });

        // 5. Add dynamic scoring columns to challenges
        Schema::table('ctf_challenges', function (Blueprint $table) {
            $table->boolean('dynamic_scoring')->default(false)->after('tags');
            $table->integer('min_points')->nullable()->after('dynamic_scoring');
            $table->integer('decay_factor')->default(0)->after('min_points');
            $table->integer('solve_count')->default(0)->after('decay_factor');
        });

        // 6. Add team support to submissions (unique already removed in prior migration)
        Schema::table('ctf_submissions', function (Blueprint $table) {
            $table->foreignId('ctf_team_id')->nullable()->constrained()->nullOnDelete()->after('user_id');
        });

        // 7. Add solves tracking for dynamic scoring
        Schema::create('ctf_challenge_solves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_challenge_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ctf_team_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('solve_order');
            $table->integer('points_awarded');
            $table->timestamp('solved_at');
            $table->timestamps();
            $table->unique(['ctf_challenge_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ctf_challenge_solves');
        Schema::dropIfExists('ctf_hint_purchases');
        Schema::dropIfExists('ctf_challenge_files');
        Schema::dropIfExists('ctf_team_members');
        Schema::dropIfExists('ctf_teams');

        Schema::table('ctf_submissions', function (Blueprint $table) {
            $table->dropForeign(['ctf_team_id']);
            $table->dropColumn('ctf_team_id');
        });

        Schema::table('ctf_challenges', function (Blueprint $table) {
            $table->dropColumn(['dynamic_scoring', 'min_points', 'decay_factor', 'solve_count']);
        });

        Schema::table('ctf_competitions', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn(['event_id', 'allow_teams', 'max_team_size']);
        });
    }
};
