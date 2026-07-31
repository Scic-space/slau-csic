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
            $table->json('privacy_settings')->nullable()->after('profile_photo');
            $table->text('approval_notes')->nullable()->after('privacy_settings');
            $table->foreignId('approved_by')->nullable()->after('approval_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            // Indexes for performance
            $table->index('approved_at');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['approved_at']);
            $table->dropIndex(['approved_by']);
            $table->dropColumn(['privacy_settings', 'approval_notes', 'approved_by', 'approved_at']);
        });
    }
};
