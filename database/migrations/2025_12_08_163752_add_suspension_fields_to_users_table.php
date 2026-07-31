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
            $table->string('suspension_reason')->nullable()->after('approved_at');
            $table->timestamp('suspended_until')->nullable()->after('suspension_reason');
            $table->foreignId('suspended_by')->nullable()->after('suspended_until')->constrained('users')->nullOnDelete();

            // Indexes
            $table->index('suspended_until');
            $table->index('suspended_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropIndex(['suspended_until']);
            $table->dropIndex(['suspended_by']);
            $table->dropColumn(['suspension_reason', 'suspended_until', 'suspended_by']);
        });
    }
};
