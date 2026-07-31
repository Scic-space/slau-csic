<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_bank_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->text('question_text');
            $table->text('code_block')->nullable();
            $table->string('code_language')->nullable();
            $table->integer('marks')->default(1);
            $table->text('explanation')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('question_bank_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('question_bank_questions')->onDelete('cascade');
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bank_options');
        Schema::dropIfExists('question_bank_questions');
    }
};
