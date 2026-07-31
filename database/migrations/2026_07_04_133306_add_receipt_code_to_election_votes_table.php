<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_votes', function (Blueprint $table) {
            $table->string('receipt_code', 64)->nullable()->unique()->after('election_candidate_id');
        });
    }

    public function down(): void
    {
        Schema::table('election_votes', function (Blueprint $table) {
            $table->dropColumn('receipt_code');
        });
    }
};
