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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('objectives')->nullable();
            $table->enum('type', ['research', 'development', 'ctf', 'competition', 'community', 'security_audit']);
            $table->enum('status', ['proposed', 'active', 'on_hold', 'completed', 'cancelled'])->default('proposed');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->foreignId('lead_id')->constrained('users');
            $table->json('tags')->nullable(); // ['web-security', 'ctf', 'python']
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
