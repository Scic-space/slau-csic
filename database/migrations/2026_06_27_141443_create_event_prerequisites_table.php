<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prerequisite_event_id')->nullable()->constrained('events')->cascadeOnDelete();
            $table->foreignId('required_badge_id')->nullable()->constrained('badges')->cascadeOnDelete();
            $table->string('required_skill_level', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_prerequisites');
    }
};
