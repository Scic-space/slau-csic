<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->unique()->constrained('exam_attempts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->boolean('eligible')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('exam_id');
            $table->index('eligible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_eligibilities');
    }
};
