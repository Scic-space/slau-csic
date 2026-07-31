<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_lead')->default(false);
            $table->boolean('is_backup')->default(false);
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->text('reasoning')->nullable();
            $table->json('conflict_flags')->nullable();
            $table->string('status')->default('suggested');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['assignment_role_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_members');
    }
};
