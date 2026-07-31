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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('type', ['workshop', 'competition', 'ctf', 'bootcamp', 'awareness_campaign', 'talk', 'social', 'hackathon']);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('location');
            $table->string('banner_image')->nullable();
            $table->json('gallery')->nullable(); // Multiple images
            $table->integer('max_participants')->nullable();
            $table->boolean('registration_required')->default(true);
            $table->boolean('is_public')->default(true); // Public events shown on website
            $table->dateTime('registration_deadline')->nullable();
            $table->enum('status', ['draft', 'published', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('organizer_id')->constrained('users');
            $table->text('requirements')->nullable();
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->string('external_link')->nullable(); // Registration link
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
