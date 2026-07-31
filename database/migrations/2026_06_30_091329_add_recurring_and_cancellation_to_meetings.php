<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('late_threshold_minutes');
            $table->foreignId('parent_meeting_id')->nullable()->constrained('meetings')->nullOnDelete()->after('is_recurring');
            $table->timestamp('cancelled_at')->nullable()->after('minutes');
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');
            $table->string('minutes_status')->default('draft')->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'parent_meeting_id', 'cancelled_at', 'cancellation_reason', 'minutes_status']);
        });
    }
};
