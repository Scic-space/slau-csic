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
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('location')->nullable()->change();
            $table->integer('expected_attendees')->nullable()->default(0)->change();
            $table->integer('code_expires_minutes')->nullable()->default(30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('location')->nullable(false)->change();
            $table->integer('expected_attendees')->nullable(false)->default(0)->change();
            $table->integer('code_expires_minutes')->nullable(false)->default(30)->change();
        });
    }
};
