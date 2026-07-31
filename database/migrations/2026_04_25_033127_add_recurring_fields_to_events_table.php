<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('external_link');
            $table->unsignedBigInteger('parent_event_id')->nullable()->after('is_recurring');
            $table->timestamp('cancelled_at')->nullable()->after('parent_event_id');
            $table->foreign('parent_event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['parent_event_id']);
            $table->dropColumn(['is_recurring', 'parent_event_id', 'cancelled_at']);
        });
    }
};
