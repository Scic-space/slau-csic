<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove placeholder announcements that were created as test data
     * so they no longer surface on the public pages.
     */
    public function up(): void
    {
        DB::table('announcements')
            ->where('title', 'like', 'Past Announcement%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
