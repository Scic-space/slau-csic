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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('force_password_change')->default(false);
            $table->foreignId('password_reset_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('password_reset_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['password_reset_by']);
            $table->dropColumn(['force_password_change', 'password_reset_by', 'password_reset_at']);
        });
    }
};
