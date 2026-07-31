<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_question_id')->constrained()->onDelete('cascade');
            $table->text('answer_text')->nullable();
            $table->foreignId('selected_option_id')->nullable()->constrained('question_bank_options')->onDelete('cascade');
            $table->boolean('is_correct')->nullable();
            $table->integer('marks_awarded')->default(0);
            $table->timestamps();

            $table->index('exam_attempt_id');
            $table->index('exam_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
