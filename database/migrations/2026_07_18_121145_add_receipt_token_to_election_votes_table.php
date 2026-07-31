<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('election_votes', function (Blueprint $table) {
            $table->string('receipt_token', 12)->nullable()->after('receipt_code');
        });
    }

    public function down(): void
    {
        Schema::table('election_votes', function (Blueprint $table) {
            $table->dropColumn('receipt_token');
        });
    }
};
