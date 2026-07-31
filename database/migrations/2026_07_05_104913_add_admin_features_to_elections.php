<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->boolean('is_test_ballot')->default(false)->after('allow_vote_changes');
            $table->dateTime('results_publish_at')->nullable()->after('is_test_ballot');
        });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropColumn(['is_test_ballot', 'results_publish_at']);
        });
    }
};
