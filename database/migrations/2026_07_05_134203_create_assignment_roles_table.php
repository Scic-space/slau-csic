<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('seats_required')->default(1);
            $table->integer('seats_filled')->default(0);
            $table->json('required_skills')->nullable();
            $table->boolean('is_lead_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_roles');
    }
};
