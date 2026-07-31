<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('time_remaining_seconds')->nullable();
            $table->integer('total_score')->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamps();

            $table->unique(['exam_id', 'user_id']);
            $table->index('exam_id');
            $table->index('user_id');
            $table->index('passed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
