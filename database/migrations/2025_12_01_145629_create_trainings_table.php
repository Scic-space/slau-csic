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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('category', ['ethical_hacking', 'digital_forensics', 'network_security', 'web_security', 'mobile_security', 'ctf', 'programming', 'other']);
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced']);
            $table->text('objectives')->nullable();
            $table->text('prerequisites')->nullable();
            $table->integer('duration_hours')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('resources')->nullable(); // [{type: 'video', url: '...'}, {type: 'pdf', url: '...'}]
            $table->integer('max_enrollments')->nullable();
            $table->boolean('is_published')->default(false);
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();
            $table->foreignId('instructor_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
