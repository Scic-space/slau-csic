<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('requires_approval');
            $table->string('recurring_frequency')->nullable()->after('is_recurring');
            $table->integer('recurring_interval')->nullable()->after('recurring_frequency');
            $table->date('recurring_end_date')->nullable()->after('recurring_interval');
            $table->date('recurring_last_generated')->nullable()->after('recurring_end_date');
            $table->unsignedBigInteger('recurring_parent_id')->nullable()->after('recurring_last_generated');

            $table->index('is_recurring');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'is_recurring',
                'recurring_frequency',
                'recurring_interval',
                'recurring_end_date',
                'recurring_last_generated',
                'recurring_parent_id',
            ]);
        });
    }
};
