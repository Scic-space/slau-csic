<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->timestamp('applications_starts_at')->nullable()->after('allow_vote_changes');
            $table->timestamp('applications_ends_at')->nullable()->after('applications_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn(['applications_starts_at', 'applications_ends_at']);
        });
    }
};
