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
        Schema::table('fine_payments', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('receipt_number');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete()->after('recorded_by');
            $table->string('status', 20)->default('recorded')->after('notes');
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->timestamp('rejected_at')->nullable()->after('confirmed_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fine_payments', function (Blueprint $table) {
            $table->dropColumn(['receipt_path', 'submitted_by', 'status', 'confirmed_at', 'rejected_at', 'rejection_reason']);
        });
    }
};
