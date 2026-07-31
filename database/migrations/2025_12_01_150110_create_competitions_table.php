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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['ctf', 'hackathon', 'coding', 'cybersecurity']);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('location');
            $table->string('website_url')->nullable();
            $table->boolean('is_team_based')->default(true);
            $table->integer('max_team_size')->nullable();
            $table->enum('participation_status', ['registered', 'participating', 'completed'])->default('registered');
            $table->integer('club_ranking')->nullable();
            $table->text('achievements')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
