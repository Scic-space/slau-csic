<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ctf_competitions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_public')->default(true);
            $table->integer('max_score')->nullable();
            $table->timestamps();
        });

        Schema::create('ctf_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#10b981');
            $table->string('icon')->default('🏴');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ctf_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_competition_id')->constrained()->onDelete('cascade');
            $table->foreignId('ctf_category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('flag_hash');
            $table->integer('points')->default(100);
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'insane'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->text('hint')->nullable();
            $table->integer('hint_cost')->default(0);
            $table->integer('max_attempts')->default(0);
            $table->json('tags')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['ctf_competition_id', 'slug']);
        });

        Schema::create('ctf_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_challenge_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('submitted_flag');
            $table->boolean('is_correct');
            $table->integer('points_awarded')->default(0);
            $table->integer('attempt_number')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['ctf_challenge_id', 'user_id']);
            $table->index(['ctf_challenge_id', 'is_correct']);
            $table->index(['user_id', 'is_correct']);
        });

        Schema::create('ctf_writeups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctf_challenge_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->longText('content');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['ctf_challenge_id', 'user_id']);
        });

        // Seed default categories
        $categories = [
            ['name' => 'Web', 'slug' => 'web', 'color' => '#3b82f6', 'icon' => '🏴', 'sort_order' => 0],
            ['name' => 'Crypto', 'slug' => 'crypto', 'color' => '#8b5cf6', 'icon' => '🏴', 'sort_order' => 1],
            ['name' => 'Forensics', 'slug' => 'forensics', 'color' => '#f59e0b', 'icon' => '🏴', 'sort_order' => 2],
            ['name' => 'PWN', 'slug' => 'pwn', 'color' => '#ef4444', 'icon' => '🏴', 'sort_order' => 3],
            ['name' => 'Reversing', 'slug' => 'reversing', 'color' => '#06b6d4', 'icon' => '🏴', 'sort_order' => 4],
            ['name' => 'OSINT', 'slug' => 'osint', 'color' => '#10b981', 'icon' => '🏴', 'sort_order' => 5],
            ['name' => 'Misc', 'slug' => 'misc', 'color' => '#6b7280', 'icon' => '🏴', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            DB::table('ctf_categories')->insert(array_merge($category, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctf_writeups');
        Schema::dropIfExists('ctf_submissions');
        Schema::dropIfExists('ctf_challenges');
        Schema::dropIfExists('ctf_categories');
        Schema::dropIfExists('ctf_competitions');
    }
};
