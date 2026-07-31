<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('target_type')->default('custom');
            $table->unsignedBigInteger('target_id')->nullable()->index();
            $table->dateTime('deadline')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('draft');
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->decimal('fairness_score', 5, 2)->nullable();
            $table->json('policy_weights')->nullable();
            $table->text('context_notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
