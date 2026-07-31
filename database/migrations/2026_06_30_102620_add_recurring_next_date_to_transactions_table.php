<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('recurring_next_date')->nullable()->after('recurring_last_generated');
            $table->index('recurring_next_date');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['recurring_next_date']);
            $table->dropColumn('recurring_next_date');
        });
    }
};
