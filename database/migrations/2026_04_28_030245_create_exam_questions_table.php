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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_bank_question_id')->constrained('question_bank_questions')->onDelete('cascade');
            $table->integer('custom_marks')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['exam_id', 'question_bank_question_id']);
            $table->index('exam_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
