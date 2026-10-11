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
            $table->enum('membership_status', [
                'pending',
                'active',
                'suspended',
                'inactive',
                'rejected',
                'revoked',
                'left',
            ])->default('pending')->change();

            $table->enum('membership_type', [
                'active',
                'associate',
                'alumni',
            ])->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('membership_status', [
                'pending',
                'active',
                'suspended',
                'revoked',
            ])->default('pending')->change();

            $table->enum('membership_type', [
                'active',
                'associate',
            ])->default('active')->change();
        });
    }
};
